<?php

namespace simplerest\tests;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use simplerest\models\UsersModel;
use simplerest\libs\DB;
use simplerest\libs\Debug;

define('HOST', 'simplerest.lan');
define('BASE_URL', 'http://'. HOST .'/');

// API UNIT TEST
class ApiTest extends TestCase
{   
    private $uid;
    private $at;
    private $rt;

	private function login($credentials){
		$ch = curl_init();

        $data = json_encode($credentials);

        curl_setopt_array($ch, array(
        CURLOPT_URL => BASE_URL . "auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Length: ". strlen($data),
            "Content-Type: text/plain",
            "Host: " . HOST,
            "cache-control: no-cache"
            ),
        ));

		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			throw new \Exception("$error_msg ($http_code)");
		}
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

		$res = json_decode($response, true);
		
        if (isset($res['error']) && !empty($res['error']))
            throw new \Exception($res['error']);	
        
        return [$res['data']['access_token'], $res['data']['refresh_token']];
    }
    
    private function get_me(string $at){
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/me",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

      
        if (!isset($res['data']['id']) || !isset($res['data']['email']))
            throw new \Exception("Empty uid or email");       

        // [id, username, emai,... ]
        return $res['data'];
    }

    function __construct() {
		parent::__construct();
        $this->config = include 'config/config.php';

        list($this->at, $this->rt) = $this->login(['email' => "tester3@g.c", "password" => "gogogo"]);
        $this->uid = $this->get_me($this->at)['id'];
    }

    /*
        /api/v1/me
        Case: OK
    */
	function testgetme()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/me",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']['id']) && isset($res['data']['email'])
        );

    }
    
    /*
        /api/v1/products
    */
	function testgetproducts()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where(['belongs_to', $this->uid])->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }
    
    
    /*
        get
        Case: found
    */
    function testgetproduct()
    {
        $ch = curl_init();
        
        $id  = DB::table('products')->where(['belongs_to' => $this->uid])->random()->value('id');

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products/$id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $error_msg = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {           
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['error']) && !isset($res['paginator'])
        );

        $this->assertTrue(
            empty($res['error'])
        );

        $item = DB::table('products')->where(['id', $id])->setFetchMode('ASSOC')->first();
        //Debug::dd(DB::getQueryLog());

        //Debug::dd($item);
        //Debug::dd($res['data']);

        $this->assertEquals($item, $res['data']); 
    }

    /*
        get
        Case: not found
    */
    function testgetproductnotfound()
    {
        $model = DB::table('products');
        $idn = $model->getIdName();
        $non_existing_id = $model->max($idn) + 1;

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products/$non_existing_id",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $error_msg = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        $this->assertEquals($http_code, 404);
    }

    function testpagesize1a()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $error_msg = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {           
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['error']) && isset($res['paginator'])
        );

        $this->assertTrue(
            empty($res['error'])
        );

        $cnt = DB::table('products')->count();

        $this->assertTrue(
            count($res['data']) == min($this->config['paginator']['default_limit'], $cnt)
        );
    }

    function testpagesize1b()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?pageSize=5",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $error_msg = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {           
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['error']) && isset($res['paginator'])
        );

        $this->assertTrue(
            empty($res['error'])
        );

        $cnt = DB::table('products')->count();

        $this->assertTrue(
            count($res['data']) == min(5, $cnt)
        );
    }

    private function get_rand_fields($table, $count = 1, $nullables = false){
        $m = DB::table($table);

        $ff = $m->getNotHidden();
        $ff = array_diff($ff, ['belongs_to', 'created_at', 'deleted_at', 'updated_at', 'id']);

        if (!$nullables)
            $ff = array_intersect($ff, $m->getNotNullables());

        $ixs  = array_rand($ff, $count);

        $fields = [];
        foreach ((array) $ixs as $ix){
            $fields[] = $ff[$ix];
        }

        return $fields;
    }

    private function get_rand_vals($table, $field, $count = 1, $user_id = null){
        $m = DB::table($table);

        if ($user_id != null)
            $m->where(['belongs_to', $user_id]);

        return $m->random()->limit($count)->pluck($field);
    }

    private function get_rand($table, $num_values = 1, $nullables = false){
        $m = DB::table($table);

        $ff = $m->getNotHidden();
        $ff = array_diff($ff, ['belongs_to', 'deleted_at', 'id']);
        
        if ($nullables)
            $ff = array_intersect($ff, $m->getNullables());

        $values = [];

        $field  = $ff[array_rand($ff, 1)];

        while (count($values) < $num_values){
            $stuck  = false;
            $val    = null; 

            while ($val == null || $stuck){
                $i = 0;
                $stuck = false;

                if ($stuck){
                    $field = $ff[array_rand($ff, 1)];
                }
                
                $val = DB::table($table)->whereNotNull($field)->random()->value($field);
                while(trim($val) == ''){
                    $i++;
                    $val = DB::table($table)->whereNotNull($field)->random()->value($field);
                    if ($i>5){
                        $stuck = true;
                        break;
                    }                
                }
                //
                $ff = array_diff($ff, [$field]);
                $values[] = $val;
            }

        }
            
        return [$field, $values];
    }

    function testfilter001()
    {
        list($field, $vals) =  $this->get_rand('products');

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?$field=". urlencode($vals[0]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')
        ->where(['belongs_to' => $this->uid, $field => $vals[0]])->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter001b()
    {
        list($field, $vals) = $this->get_rand('products');

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?{$field}[eq]=". urlencode($vals[0]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')
        ->where(['belongs_to' => $this->uid, $field => $vals[0]])->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        if ($model_arr != $res['data']){
            Debug::dd(DB::getQueryLog());
            Debug::dd($model_arr, 'MODELO:');
            Debug::dd(BASE_URL . "api/v1/products?{$field}[eq]=". urlencode($vals[0]));
            Debug::dd($res['data'], 'API response:');
        }

        $this->assertEquals($model_arr,$res['data']);
    }

    function testfilter002()
    {
        $fields = $this->get_rand_fields('products', 2);

        $values = DB::table('products')
        ->random()
        ->where(['belongs_to', $this->uid])
        ->select($fields)->first();

        //Debug::dd($values);

        $fv = [];
        $w  = [];
        
        foreach ($fields as $field){
            $fv[] = $field . '=' . urlencode($values->$field);

            if ($values->$field == NULL){
                $op = 'IS NULL';
            }else
                $op = '=';                

            $w[] = [$field, $values->$field, $op]; 
        }
        
        $url_params = implode('&', $fv);
        //Debug::dd($url_params);

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?$url_params",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')
        ->where(['belongs_to', $this->uid])
        ->where($w)->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        //Debug::dd(DB::getQueryLog()); 

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter003()
    {
        $field  = $this->get_rand_fields('products')[0];
        $values = $this->get_rand_vals('products', $field, 2, $this->uid);

        $values_str = implode(',', array_map('urlencode',$values));
        //Debug::dd("api/v1/products?{$field}[in]=$values_str");

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?{$field}[in]=$values_str",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            [$field, $values ]
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr, $res['data']); 
    }

    function testfilter003b()
    {
        $field  = $this->get_rand_fields('products')[0];
        $values = $this->get_rand_vals('products', $field, 2, $this->uid);

        $values_str = implode(',', array_map('urlencode',$values));
        //Debug::dd("api/v1/products?$field=$values_str");

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?$field=$values_str",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            [$field, $values ]
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr, $res['data']);     
    }

    function testfilter004()
    {
        $field  = $this->get_rand_fields('products')[0];
        $values = $this->get_rand_vals('products', $field, 2, $this->uid);

        $values_str = implode(',', array_map('urlencode',$values));
        //Debug::dd("api/v1/products?{$field}[in]=$values_str");

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?{$field}[notIn]=$values_str",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            [$field, $values, 'NOT IN' ]
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr, $res['data']);
    }

    function testfilter006()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?name[startsWith]=Caja%20",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['name', 'Caja %', 'LIKE']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter007()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?name[notStartsWith]=Caja%20",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $res = json_decode($response, true);

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['name', 'Caja %', 'NOT LIKE']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter008()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?name[endsWith]=ora",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        //Debug::dd($res, 'Response:');

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['name', '%ora', 'LIKE']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter009()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?name[notEndsWith]=ora",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        //Debug::dd($res, 'Response:');

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['name', '%ora', 'NOT LIKE']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter010()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?name[contains]=%20de%20",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['name', '% de %', 'LIKE']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter011()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?name[notContains]=%20de%20",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['name', '% de %', 'NOT LIKE']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    /*
        Comparadores numéricos
    */

    function testfilter012()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?cost[neq]=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['cost', 100, '!=']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter013()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?cost[gt]=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['cost', 100, '>']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter014()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?cost[lt]=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['cost', 100, '<']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter015()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?cost[gteq]=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['cost', 100, '>=']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter016()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?cost[lteq]=100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['cost', 100, '<=']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter017()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?order[cost]=ASC&cost[between]=50,100",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['cost', 100, '<='],
            ['cost', 50, '>=']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->orderBy(['cost' => 'ASC'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter018()
    {
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?order[created_at]=ASC&created_at[between]=2019-11-02+16%3A07%3A10,2019-11-03+21%3A33%3A20",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid], 
            ['created_at', '2019-11-02 16:07:10', '>='],
            ['created_at', '2019-11-03 21:33:20', '<=']
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->orderBy(['created_at' => 'ASC'])->get();

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter019()
    {
        /*
            Elegir N campos al azar.....
        */
        $not_hidden = DB::table('products')->getNotHidden();
        $cnt = count($not_hidden);
        $cnt = $cnt > 1 ? ceil($cnt / 2)  : $cnt;
        $ixs = array_rand($not_hidden, $cnt);

        $fields = [];
        foreach ($ixs as $ix){
            $fields[] = $not_hidden[$ix];
        }
        
        $fields_str = implode(',',$fields);

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?fields=$fields_str",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $this->assertTrue(
            isset($res['data']) && isset($res['paginator'])
        );

        $model_arr = DB::table('products')->where([
            ['belongs_to', $this->uid]
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get($fields);

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter020()
    {
        $id  = DB::table('products')->where([
            ['belongs_to', $this->uid]
        ])->random()->value('id');

        /*
            Elegir N campos al azar.....
        */
        $not_hidden = DB::table('products')->getNotHidden();
        $cnt = count($not_hidden);
        $cnt = $cnt > 1 ? ceil($cnt / 2)  : $cnt;
        $ixs = array_rand($not_hidden, $cnt);

        $fields = [];
        foreach ($ixs as $ix){
            $fields[] = $not_hidden[$ix];
        }

        $fields_str = implode(',',$fields);

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products/$id?fields=$fields_str",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $model_arr = DB::table('products')->where([
            'id' => $id
        ])
        ->setFetchMode('ASSOC')->first($fields);

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter021()
    {
        $id  = DB::table('products')->where([
            ['belongs_to', $this->uid]
        ])->random()->value('id');

        $not_hidden = DB::table('products')->getNotHidden();
        $field = $not_hidden[array_rand($not_hidden,1)];

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products/$id?exclude=$field",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $model_arr = DB::table('products')->where([
            'id' => $id
        ])
        ->setFetchMode('ASSOC')->hide([$field])->first();

        //Debug::dd(DB::getQueryLog()); 

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter022()
    {
        $nullables = DB::table('products')->getNullables();
        if (count($nullables) == 0)
            return;

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?{$nullables[0]}=NULL",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $model_arr = DB::table('products')
        ->where(['belongs_to' => $this->uid])
        ->where([ 
            $nullables[0] => NULL
        ])
        ->setFetchMode('ASSOC')->get();

        //Debug::dd(DB::getQueryLog()); 

        $this->assertEquals($model_arr,$res['data']); 
    }

    function testfilter023()
    {
        $nullables = DB::table('products')->getNullables();
        if (count($nullables) == 0)
            return;

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => BASE_URL . "api/v1/products?{$nullables[0]}[neq]=NULL",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Accept: */*",
                "Accept-Encoding: gzip, deflate",
                "Cache-Control: no-cache",
                "Connection: keep-alive",
                "Authorization: Bearer $this->at",
                "Content-Type: text/plain",
                "Host: " . HOST,
                "cache-control: no-cache"
                ),
            ));

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $res = json_decode($response, true);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);

            if (isset($res['error']))
                Debug::dd($res['error']);

            throw new \Exception("$error_msg ($http_code)");
        }
        
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

        $model_arr = DB::table('products')
        ->where(['belongs_to', $this->uid])
        ->where([ 
            $nullables[0],  NULL, 'IS NOT'
        ])
        ->setFetchMode('ASSOC')->limit($this->config['paginator']['default_limit'])->get();

        //Debug::dd(DB::getQueryLog()); 
        //Debug::dd($model_arr);
        //Debug::dd($res['data']);

        $this->assertEquals($model_arr, $res['data']); 
    }

}
