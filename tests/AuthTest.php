<?php

namespace simplerest\tests;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use simplerest\models\UsersModel;
use simplerest\models\RolesModel;
use simplerest\libs\DB;
use simplerest\libs\Debug;


define('HOST', 'simplerest.lan');
define('BASE_URL', 'http://'. HOST .'/');

class AuthTest extends TestCase
{   

	function __construct() {
		parent::__construct();
        $this->config = include 'config/config.php';
    }

    /*
        @param string access_token
    */
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
                "Authorization: Bearer $at",
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


	/*
		Case: OK
	*/
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

		$this->assertTrue(
            isset($res['data']['access_token']) && isset($res['data']['refresh_token']) && isset($res['data']['expires_in'])
		);		

		$at_payload = \Firebase\JWT\JWT::decode($res['data']['access_token'], $this->config['access_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);

		$this->assertTrue(
			isset($at_payload->uid) && isset($at_payload->roles)
		);		

		$rt_payload = \Firebase\JWT\JWT::decode($res['data']['refresh_token'], $this->config['refresh_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);

		$this->assertTrue(
			isset($rt_payload->uid) && isset($rt_payload->roles)
		);

        $uid = $this->get_me($res['data']['access_token'])['id'];

        $role_ids = DB::table('user_roles')->where(['user_id' => $uid])->pluck('role_id');
        $rm = new RolesModel();

        foreach ($role_ids as $role_id){
            $this->assertTrue(
                in_array($rm->getRoleName($role_id), $rt_payload->roles)
            );
        }

	}

	function testlogin1()
    {
        $this->login(['email' => "tester3@g.c", "password" => "gogogo"]);     
	}
	
	function testlogin1b()
    {
        $this->login(['username' => "tester3", "password" => "gogogo"]);
	}

	/*
		Case: wrong user or password
	*/
	function testlogin2()
    {
		$ch = curl_init();

        $obj  = ['email' => "tester3@g.c", "password" => "xxx"];
        $data = json_encode($obj);

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

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check HTTP status code
        if ($err){
            throw new \Exception("$err ($http_code)");
        }
		
		$this->expectException(\Exception::class);
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

	}

	/*
		Case: wrong user or password
	*/
	function testlogin2b()
    {
		$ch = curl_init();

        $obj  = ['email' => "0as030dff0", "password" => "0as030dff0"];
        $data = json_encode($obj);

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

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check HTTP status code
        if ($err){
            throw new \Exception("$err ($http_code)");
        }
		
		$this->expectException(\Exception::class);
        if ($http_code != 200)
            throw new \Exception("Unexpected http code ($http_code)");

	}

	/*
		Case: OK
	*/
	function testregister1()
    {
		$ch = curl_init();

		$username = 'asdf_0000';
		$email = "$username@g.c";

        $obj  = ['username' => $username, "email" => $email, "password" => "asdf1234"];
        $data = json_encode($obj);

        curl_setopt_array($ch, array(
        CURLOPT_URL => BASE_URL . "auth/register",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FAILONERROR => true,
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
			)		
		));
		
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

		$this->assertTrue(
            isset($res['data']['access_token']) && isset($res['data']['refresh_token']) && isset($res['data']['expires_in'])
		);
		
		$at_payload = \Firebase\JWT\JWT::decode($res['data']['access_token'], $this->config['access_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);

		$this->assertTrue(
			isset($at_payload->uid) && isset($at_payload->roles)
		);		

		$rt_payload = \Firebase\JWT\JWT::decode($res['data']['refresh_token'], $this->config['refresh_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);

		$this->assertTrue(
			isset($rt_payload->uid) && isset($rt_payload->roles)
		);

		$uid = DB::table('users')
		->where(['email' => $email])->value('id');

		DB::table('user_roles')
		->where(['user_id' => $uid])->delete(false);

		DB::table('users')
		->where(['id' => $uid])->delete(false);
	}

	function testtokenrenew()
    {
		$ch = curl_init();

		// First, login to get a refresh token

		$obj  = ['email' => "tester3@g.c", "password" => "gogogo"];
        $data = json_encode($obj);

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

		$this->assertTrue(
            isset($res['data']['access_token']) && isset($res['data']['refresh_token']) && isset($res['data']['expires_in'])
		);

		/////////////  token renew /////////////

        $rt = $res['data']['refresh_token'];

        curl_setopt_array($ch, array(
        CURLOPT_URL => BASE_URL . "auth/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => array(
            "Accept: */*",
			"Accept-Encoding: gzip, deflate",
			"Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCIsImlhdCI6MTU3NTYzOTQwMiwiZXhwIjoxODkwOTk5NDAyLCJ1aWQiOiIyMjMiLCJyb2xlcyI6WyJyZWd1bGFyIl0sImNvbmZpcm1lZF9lbWFpbCI6MH0.b6kG91ezGmYamMRw86zR7-7nTYvWUzNezUAXs5EMbF8",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
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

		$this->assertTrue(
            isset($res['data']['access_token']) && isset($res['data']['expires_in'])
		);		

		$at_payload = \Firebase\JWT\JWT::decode($res['data']['access_token'], $this->config['access_token']['secret_key'], [ $this->config['access_token']['encryption'] ]);

		$this->assertTrue(
			isset($at_payload->uid) && isset($at_payload->roles)
		);		
	}
	
	
}
