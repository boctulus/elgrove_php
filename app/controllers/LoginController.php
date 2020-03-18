<?php

namespace simplerest\controllers;

use simplerest\libs\Debug;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\models\UsersModel;
use simplerest\models\RolesModel;
use simplerest\libs\Utils;

class LoginController extends MyController
{
	function index(){
		$this->login();
	}
	
	function login(){	
		$this->view('login.php', [ 'title'=>'Ingreso', 'hidenav'=> true ]);
	}
	
	function register(){
		$this->view('register.php', ['title'=>'Registro', 'hidenav'=> true]);
	}

	/*
		callback
	*/
	function google_login()
	{
		$google_ctrl = new GoogleController();
		$res = $google_ctrl->login_or_register();

		if (isset($res['data'])){
			$this->view('generic.php', [
				'title'=>'Google login', 
				'hidenav'=> true,
				'access_token' => $res['data']['access_token'],
				'expires_in' => $res['data']['expires_in'],
				'refresh_token' => $res['data']['refresh_token']
			]);
		}else {
			$this->view('generic.php', [
				'title'=>'Google login', 
				'hidenav'=> false,
				'error' => $res['error']
			]);
		}		
	}
	
	/*
		callback
	*/
	function fb_login(){

		$fb_ctrl = new FacebookController();
		$res = $fb_ctrl->login_or_register();

		session_destroy();

		if (isset($res['data'])){
			$this->view('generic.php', [
				'title'=>'Facebook login', 
				'hidenav'=> true,
				'access_token' => $res['data']['access_token'],
				'expires_in' => $res['data']['expires_in'],
				'refresh_token' => $res['data']['refresh_token']
			]);
		}else {
			$this->view('generic.php', [
				'title'=>'Facebook login', 
				'hidenav'=> false,
				'error' => $res['error']
			]);
		}		
		
	}

	/*
        JWT token for email confirmation & remember me
    */
    protected function gen_jwt2(string $email, string $secret_key, string $encryption, string $exp_time){
        $time = time();

        $payload = [
            'alg' => $encryption,
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $exp_time,
            'email' => $email
        ];

        return \Firebase\JWT\JWT::encode($payload, $secret_key,  $encryption);
    }

	protected function gen_jwt(array $props, string $token_type){
        $time = time();

        $payload = [
            'alg' => $this->config[$token_type]['encryption'],
            'typ' => 'JWT',
            'iat' => $time, 
            'exp' => $time + $this->config[$token_type]['expiration_time']
        ];
        
        $payload = array_merge($payload, $props);

        return \Firebase\JWT\JWT::encode($payload, $this->config[$token_type]['secret_key'],  $this->config[$token_type]['encryption']);
    }

	function rememberme(){
		$this->view('rememberme.php', ['title'=>'Recuérdame', 'hidenav'=> true]);
	}

	/*
		Las urls para cambio de correo son muy similares a esta:

		//simplerest.lan/login/confirm_email/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3MjI2OTE4NiwiZXhwIjoxNTcyODczOTg2LCJlbWFpbCI6InBlcGVAZ21haWwuY29tIn0.fl_jVsAe16ePinDY0QT8GRK_cuk0Ebn3CVNfCgfnM3s/1572873986

		Solo cambia el nombre del "action" por change_pass :

		//simplerest.lan/login/change_pass/eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3MjI2OTE4NiwiZXhwIjoxNTcyODczOTg2LCJlbWFpbCI6InBlcGVAZ21haWwuY29tIn0.fl_jVsAe16ePinDY0QT8GRK_cuk0Ebn3CVNfCgfnM3s/1572873986

	*/
	function rememberme_process(){
		$data  = Factory::request()->getBody(false);

		if ($data == null)
			Factory::response()->sendError('Invalid JSON',400);
	
		$email = $data->email ?? null;

		if ($email == null)
			Factory::response()->sendError('Empty email', 400);

		try {	

			$u = (DB::table('users'))->setFetchMode('ASSOC');
			$rows = $u->where(['email', $email])->get(['id']);

			if (count($rows) === 0)
				Factory::response()->send('Email not found'); // no enviar este mensaje
			
			$exp = time() + $this->config['email']['expires_in'];	

			$base_url =  HTTP_PROTOCOL . '://' . $_SERVER['HTTP_HOST'];

			$token = $this->gen_jwt2($email, $this->config['email']['secret_key'], $this->config['email']['encryption'], $this->config['email']['expires_in'] );
			$url = $base_url . '/login/change_pass/' . $token . '/' . $exp; 

			// Queue email
			$ok = (bool) DB::table('messages')->create([
				'from_email' => $this->config['email']['mailer']['from'][0],
				'from_name' => $this->config['email']['mailer']['from'][1],
				'to_email' => $email, 
				'to_name' => '', 
				'subject' => 'Cambio de contraseña', 
				'body' => "Para cambiar la contraseña siga el enlace:<br/><a href='$url'>$url</a>"
			]);

		} catch (\Exception $e){
			Factory::response()->sendError($e->getMessage(), 500);
		}


		if (!$ok)
			Factory::response()->sendError("Error in user registration!", 500, 'Error during registration of email confirmation');

		Factory::response()->send('OK');
	}

	function rememeberme_mail_sent(){
		$this->view('rememeberme_mail_sent.php', ['title'=>'Recuérdame', 'hidenav'=> true]);
	}

	function confirm_email($jwt, $exp)
	{
		// Es menos costoso veririficar así en principio
		if ((int) $exp < time())
			$error = 'Link is outdated';
		else{

			if($jwt != null)
			{
				try {
					$payload = \Firebase\JWT\JWT::decode($jwt, $this->config['email']['secret_key'], [ $this->config['email']['encryption'] ]);
					
					if (empty($payload))
						$error = 'Unauthorized!';                     

					if (empty($payload->email)){
						$error = 'email is needed';
					}

					if ($payload->exp < time())
						$error = 'Token expired';
						
					$u = DB::table('users');
					$ok = (bool) $u->where(['email', $payload->email])
						   		->update(['confirmed_email' => 1]);
										
					//if (!$ok)		
					//	$error = 'Error en activación';				

				} catch (\Exception $e) {
					/*
					* the token was not able to be decoded.
					* this is likely because the signature was not able to be verified (tampered token)
					*
					* reach this point if token is empty or invalid
					*/
					Factory::response()->sendError($e->getMessage(),401);
				}	
			}else{
				Factory::response()->sendError('Authorization jwt token not found',400);
			}     
		}	

		if (!isset($error)){

			$rows = DB::table('users')->setFetchMode('ASSOC')->where(['email', $payload->email])->get(['id']);
			$uid  = $rows[0]['id'];

			$affected_rows = DB::table('users')->where(['id' => $uid])->update(['confirmed_email' => 1]);
			
			if ($affected_rows === false)
				Factory::response()->sendError('Error', 500);

			$rows = DB::table('user_roles')->setFetchMode('ASSOC')->where(['belongs_to', $uid])->get(['role_id as role']);	

			$r = new RolesModel();

			$roles = [];
			if (count($rows) != 0){            
				foreach ($rows as $row){
					$roles[] = $r->getRoleName($row['role']);
				}
			}else
				$roles[] = 'registered';

			$access  = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'access_token');
			$refresh = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'access_token');

			
			$this->view('generic.php', [
				'title'=>'Confirmación de correo', 
				'hidenav'=> true,
				'access_token' => $access,
				'expires_in' => $this->config['email']['expires_in'],
				'refresh_token' => $refresh
			]);
	
		}else {
			$this->view('generic.php', [
				'title'=>'Confirmación de correo fallida', 
				'hidenav'=> false,
				'error' => $error
			]);
		}	

	}

	function change_pass($jwt, $exp)
	{
		// Es menos costoso veririficar así en principio
		if ((int) $exp < time())
			$error = 'Link is outdated';
		else{

			if($jwt != null)
			{
				try {
					// Checking for token invalidation or outdated token
					
					$payload = \Firebase\JWT\JWT::decode($jwt, $this->config['email']['secret_key'], [ $this->config['email']['encryption'] ]);
					
					if (empty($payload))
						$error = 'Unauthorized!';                     

					if (empty($payload->email)){
						$error = 'email is needed';
					}

					if ($payload->exp < time())
						$error = 'Token expired';

				} catch (\Exception $e) {
					/*
					* the token was not able to be decoded.
					* this is likely because the signature was not able to be verified (tampered token)
					*
					* reach this point if token is empty or invalid
					*/
					$error = $e->getMessage();
				}	
			}else{
				$error = 'Authorization jwt token not found';
			}     
		}	

		if (!isset($error)){						
			//
			// Cargar vista 
			// donde poder setear una nueva contraseña
			//

			$this->view('update_pass.php', [
				'title'=>'Recuperación de contraseña', 
				'hidenav'=> true
			]);
	
		}else {
			$this->view('generic.php', [
				'title'=>'Recuperación de contraseña', 
				'hidenav'=> false,
				'error' => $error
			]);
		}
	}

	function change_pass_process(){
		if($_SERVER['REQUEST_METHOD']!='POST')
			Factory::response()->sendError('Incorrect verb ('.$_SERVER['REQUEST_METHOD'].'), expecting POST',405);
		
		$headers = Factory::request()->headers();
		$auth = $headers['Authorization'] ?? $headers['authorization'] ?? null;
		
		$data  = Factory::request()->getBody();
            
        if ($data == null)
			Factory::response()->sendError('Invalid JSON',400);
			
		if (!isset($data['password']) || empty($data['password']))
			Factory::response()->sendError('Empty email',400);
		
		if (empty($auth))
			Factory::response()->sendError('No auth', 400);			
			
		list($jwt) = sscanf($auth, 'Bearer %s');

		if($jwt != null)
        {
            try{
                // Checking for token invalidation or outdated token
                
                $payload = \Firebase\JWT\JWT::decode($jwt, $this->config['email']['secret_key'], [ $this->config['email']['encryption'] ]);
                
                if (empty($payload))
					Factory::response()->sendError('Unauthorized!',401);                     
					
                if (empty($payload->email)){
                    Factory::response()->sendError('Undefined email',400);
                }

                if ($payload->exp < time())
                    Factory::response()->sendError('Token expired',401);
			
				
				$rows = DB::table('users')->setFetchMode('ASSOC')->where(['email', $payload->email])->get(['id']);
				$uid = $rows[0]['id'];

				$affected = DB::table('users')->where(['id', $rows[0]['id']])->update(['password' => $data['password']]);

				// Fetch roles
				$uid = $rows[0]['id'];
				$rows = DB::table('user_roles')->setFetchMode('ASSOC')->where(['belongs_to', $uid])->get(['role_id as role']);	
				
				$r = new RolesModel();

				$roles = [];
				if (count($rows) != 0){            
					foreach ($rows as $row){
						$roles[] = $r->getRoleName($row['role']);
					}
				}else
					$roles[] = 'registered';

				
				$access  = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'access_token');
				$refresh = $this->gen_jwt(['uid' => $uid, 'roles' => $roles, 'confirmed_email' => 1], 'refresh_token');
 
				Factory::response()->send([
					'access_token' => $access,
					'expires_in' => $this->config['email']['expires_in'],
					'refresh_token' => $refresh
				]);
				
            } catch (\Exception $e) {
                /*
                * the token was not able to be decoded.
                * this is likely because the signature was not able to be verified (tampered token)
                *
                * reach this point if token is empty or invalid
                */
                Factory::response()->sendError($e->getMessage(),401);
            }	
        }else{
            Factory::response()->sendError('Authorization jwt token not found',400);
        }
       
	}
	
}


