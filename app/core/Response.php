<?php

namespace simplerest\core;

use simplerest\libs\Factory;

class Response
{
    static protected $headers = [];
    static protected $http_code = NULL;
    static protected $http_code_msg = '';
    static protected $instance = NULL;
    static protected $version = '1.1';
    static protected $config;
    static protected $pretty;
    static protected $quit = true;
    static protected $paginator;
    static protected $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;



    protected function __construct() { 
        static::$config = include CONFIG_PATH . 'config.php';
        static::$pretty = static::$config['pretty'];
    }

    static function getInstance(){
        
        if(static::$instance == NULL){
            static::$instance = new static();
        }
        return static::$instance;
    }
    
    function redirect(string $url){
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        }else
            throw new \Exception("Headers already sent in in $filename on line $line. Unable to redirect to $url");
    }

    function addHeaders(array $headers)
    {
        static::$headers = $headers;
        return static::getInstance();
    }
  
    function addHeader(string $header)
    {
        static::$headers[] = $header;
        return static::getInstance();
    }

    function code(int $http_code, string $msg = NULL)
    {
        static::$http_code_msg = $msg;
        static::$http_code = $http_code;
        return static::getInstance();
    }

    function setPretty(bool $state){
        static::$pretty = $state;
        return static::getInstance();
    }

    function setQuit(bool $state){
        static::$quit = $state;
        return static::getInstance();
    }

    function encode($data){        
        $options = static::$pretty ? static::$options | JSON_PRETTY_PRINT : static::$pretty;
            
        return json_encode($data, $options);  
    }

    function setPaginator(array $p){
        static::$paginator = $p;
        return static::getInstance();
    }

    private function zip($data){
        ob_start("ob_gzhandler");
        echo $data; 
        ob_end_flush();
    } 

    function send($data, int $http_code = NULL){
        $http_code = $http_code != NULL ? $http_code : static::$http_code;
        
        if ($http_code == NULL)
          static::$http_code;

        if ($http_code != NULL)
            header(trim('HTTP/'.static::$version.' '.$http_code.' '.static::$http_code_msg));
        
        if (is_array($data) || is_object($data)){
            $arr = ['data' => $data, 
                    'error' => '', 
                    'error_detail' => '' 
            ];

            if (static::$paginator != NULL)
                $arr['paginator'] = static::$paginator;

            $data = $this->encode($arr);
        }            

        //if (Factory::request()->gzip() && strlen($data) > 1000){
        //    $this->addHeader('Content-Encoding: gzip');
        //    $this->zip($data. "\n");
        //}else
            echo $data. "\n";

        if (static::$quit)
            exit;  	
    }

    function sendCode(int $http_code){
        http_response_code($http_code);

        if (static::$quit)
            exit; 
    }
 
    // send as JSON
    function sendJson($data, int $http_code = NULL){
        $http_code = $http_code != NULL ? $http_code : static::$http_code;
        
        if ($http_code != NULL)
            header(trim('HTTP/'.static::$version.' '.$http_code.' '.static::$http_code_msg));
       
        $res =  $this->encode([ 'data' => $data, 'error' => '', 'error_detail' => '' ]). "\n"; 
        
        if (Factory::request()->gzip() && strlen($res) > 1000){
            $this->addHeader('Content-Encoding: gzip');
            $this->zip($res);    
        }
        else
            echo $res;

        if (static::$quit)
            exit; 
    }

   
    /**
     * sendError
     *
     * @param  string $msg_error
     * @param  int $http_code
     * @param  string $error_detail
     *
     * @return void
     */
    function sendError(string $msg_error, int $http_code = NULL, $error_detail= NULL){
        if ($http_code == NULL)
            if (static::$http_code != NULL)
                $http_code = static::$http_code;
            else
                $http_code = 500;
  
        if ($http_code != NULL)
            header(trim('HTTP/'.static::$version.' '.$http_code.' '.static::$http_code_msg));

        
        $res =  $this->encode(['error' => $msg_error, 'error_detail' => $error_detail], $http_code) . "\n";
        
        if (Factory::request()->gzip() && strlen($res) > 1000){
            $this->addHeader('Content-Encoding: gzip');
            $this->zip($res);    
        }
        else
            echo $res;
        
        if (static::$quit)
            exit; 
    }
}