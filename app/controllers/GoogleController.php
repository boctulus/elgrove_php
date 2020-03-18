<?php

namespace simplerest\controllers;

use Exception;
use simplerest\core\Controller;
use simplerest\libs\DB;
use simplerest\models\UsersModel;
use simplerest\models\UserRolesModel;
use simplerest\models\RolesModel;
use simplerest\libs\Debug;

class GoogleController extends Controller
{
    protected $client;

    function __construct()
    {
        parent::__construct();

        $client = new \Google_Client();
        $client->setApplicationName('App Name');
        $client->setClientId($this->config['google_auth']['client_id']);
        $client->setClientSecret($this->config['google_auth']['client_secret']);
        $client->setRedirectUri($this->config['google_auth']['callback']);

        $client->setScopes('https://www.googleapis.com/auth/userinfo.email');
        #$client->addScope("https://www.googleapis.com/auth/drive");
        ## these two lines is important to get refresh token from google api
        $client->setAccessType('offline');
        // Set this to force to consent form to display.
        $client->setApprovalPrompt('force');
        $client->setIncludeGrantedScopes(true);

        $this->client = $client;
    }    

    function getClient(){
        return $this->client;
    }

    protected function gen_jwt(array $props, string $token_type){
        $time = time();

        $payload = [
            'alg' => $this->config[$token_type]['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $this->config[$token_type]['expiration_time'],
            'ip'  => $_SERVER['REMOTE_ADDR']
        ];
        
        $payload = array_merge($payload, $props);

        return \Firebase\JWT\JWT::encode($payload, $this->config[$token_type]['secret_key'],  $this->config[$token_type]['encryption']);
    }
    
    function login_or_register()
    {
        if (isset($_GET['code'])) {
            $auth = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (isset($auth['error']))
                return ['error' => $auth['error_description'], 'code' => 400];

            $this->client->setAccessToken($auth);
        }else
            return ['error' => 'Invalid', 'code' => 400];

        /*
        if ($this->client->isAccessTokenExpired() )    
            $auth = $this->client->getAccessToken();

            $access_token = $auth['access_token'];
            //
            ...
        }
        */

        //Debug::dd($auth);        
        /*
            Array
            (
                [access_token] => ya29.Il-iB22898tm...........

                [expires_in] => 3599

                [refresh_token] => 1//0hMcct5o95Tf-Cg.........
                
                [scope] => https://www.googleapis.com/auth/userinfo.email openid https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/drive
                
                [token_type] => Bearer
                
                [id_token] => eyJhbGciOiJSUzI1NiIsImtpZCI6IjhhNjNmZTcxZTUzMDY3NTI0Y2JiYzZhM2E1ODQ2M2IzODY0YzA3ODciLCJ0eXAiOiJKV1QifQ............
                
                [created] => 1571713360)
        */

        $payload = $this->client->verifyIdToken($auth['id_token'], $this->config['google_auth']['client_id']);
        /*
        array(14) { 
            ["iss"]=> string(27) "https://accounts.google.com" 
            ["azp"]=> string(72) "228180780767-4p8t6nvocukmu44ti57o60n1ck6sokpd.apps.googleusercontent.com" 
            ["aud"]=> string(72) "228180780767-4p8t6nvocukmu44ti57o60n1ck6sokpd.apps.googleusercontent.com" 
            ["sub"]=> string(21) "100334249456746149636" 
            ["email"]=> string(18) "boctulus@gmail.com" 
            ["email_verified"]=> bool(true) 
            ["at_hash"]=> string(22) "3nV4aKuMu_FNft90PLjXjg" ["name"]=> string(13) "Pablo Bozzolo" ["picture"]=> string(89) "https://lh3.googleusercontent.com/a-/AAuE7mATeEoHq5modzihrb_z1O-3_BhxhJ4Q9dXSKGtxNw=s96-c" 
            ["given_name"]=> string(5) "Pablo" 
            ["family_name"]=> string(7) "Bozzolo" 
            ["locale"]=> string(5) "en-GB" 
            ["iat"]=> int(1571714620) 
            ["exp"]=> int(1571718220) } 
        */

        //Debug::dd($payload);
        
        if (!$payload)
            exit;

        try 
        {        
            $conn = $this->getConnection();	
            $u = (new UsersModel($conn))->setFetchMode('ASSOC');

            $rows = $u->where(['email', $payload['email']])->get();

            if (count($rows) == 1){
                // Email already exists
                $uid = $rows[0]['id'];

                $ur = (new UserRolesModel($conn))->setFetchMode('ASSOC');
                $rows = $ur->where(['belongs_to', $uid])->get(['role_id']);

                $roles = [];
                if (count($rows) > 0){         
                    $r = new RolesModel();           
                    foreach ($rows as $row){
                        $roles[] = $r->getRoleName($row['role_id']);
                    }
                }
                    
                $_permissions = DB::table('permissions')->setFetchMode('ASSOC')->select(['tb', 'can_create as c', 'can_read as r', 'can_update as u', 'can_delete as d'])->where(['user_id' => $uid])->get();

                $perms = [];
                foreach ($_permissions as $p){
                    $tb = $p['tb'];
                    $perms[$tb] = $p['c'] * 8 + $p['r'] * 4 + $p['u'] * 2 + $p['d'];
                }

            }else{
                $data['email'] = $payload['email'];
                $data['firstname'] = $payload['given_name'] ?? NULL;
                $data['lastname'] = $payload['family_name'] ?? NULL;

                ///
                preg_match('/[^@]+/', $payload['email'], $matches);
                $username = substr($matches[0], 0, 12);
        
                $existe = DB::table('users')->where(['username', $username])->exists();
                
                if ($existe){
                    $_username = $username;
                    $append = 1;
                    while($existe){
                        $_username = $username . $append;
                        $existe = DB::table('users')->where(['username', $_username])->exists();
                        $append++;
                    }
                    $username = $_username;
                }         
        
                $data['username'] = $username;
                ///
                
                $uid = $u->create($data);
                if (empty($uid))
                    return ['error' => 'Error in user registration!', 'code' => 500];
    
                if ($u->inSchema(['belongs_to'])){
                    DB::table('users')
                    ->where(['id', $uid])
                    ->update(['belongs_to' => $uid]);
                }

                $r = new RolesModel();
                $role = $this->config['registration_role'];

                $ur = new UserRolesModel($conn);
                $id = $ur->create([ 'belongs_to' => $uid, 'role_id' => $r->get_role_id($role) ]);  // registered or other            
        
                $roles = [$role];
                $perms = [];
            }  
            

            $my_payload = [
                'uid' => $uid, 
                'roles' => $roles,
                'confirmed_email' => 1,
                'permissions' => $perms
                //'google_auth' => $auth
            ];

            $access  = $this->gen_jwt($my_payload, 'access_token');
            $refresh = $this->gen_jwt($my_payload, 'refresh_token');

            return ['code' => 200,  
                    'data' => [ 
                                'access_token'=> $access,
                                'token_type' => 'bearer', 
                                'expires_in' => $this->config['access_token']['expiration_time'],
                                'refresh_token' => $refresh                                         
                                // 'scope' => 'read write'
                    ],
                    'error' => ''
            ];
        }catch(\Exception $e){
            return ['error' => $e->getMessage(), 'code' => 500];
        }	

    }       
    
    ///////////////////////////////////////////////

    function login(){
        $client   = $this->getClient();
        $auth_url = $client->createAuthUrl();
        
        header("Location: $auth_url");
    }

	
}