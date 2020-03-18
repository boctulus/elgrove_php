<?php

namespace simplerest\core; 

use simplerest\core\interfaces\Arrayable;

class Request  implements \ArrayAccess, Arrayable
{
    static protected $query_arr;
    static protected $raw;
    static protected $params;
    static protected $headers;
    static protected $instance = NULL;

    protected function __construct() { }

    // @author limalopex.eisfux.de
    static function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach($_SERVER as $key => $val) {
          if( preg_match($rx_http, $key) ) {
            $arh_key = preg_replace($rx_http, '', $key);
            $rx_matches = array();
            // do some nasty string manipulations to restore the original letter case
            // this should work in most cases
            $rx_matches = explode('_', $arh_key);
            if( count($rx_matches) > 0 and strlen($arh_key) > 2 ) {
              foreach($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
              $arh_key = implode('-', $rx_matches);
            }
            $arh[$arh_key] = $val;
          }
        }
        return( $arh );
    }

    static function getInstance(){
        if(static::$instance == NULL){
            if (php_sapi_name() != 'cli'){
                if (isset($_SERVER['QUERY_STRING']))
					parse_str($_SERVER['QUERY_STRING'], static::$query_arr);
				
                static::$raw = file_get_contents("php://input");

                if (function_exists('apache_request_headers'))
                    static::$headers = apache_request_headers();
                else
                    static::$headers = static::apache_request_headers();
            }
            static::$instance = new static();
        }
        return static::$instance;
    }

    function setParams($params){
        static::$params = $params;
        return static::getInstance();
    }

    function headers(){
        return static::$headers;
    }

    function header($key){
        return static::$headers[$key] ?? NULL;
    }

    function gzip(){
        return in_array('gzip', explode(',', str_replace(' ', '',$this->header('Accept-Encoding'))));
    }

    function deflate(){
        return in_array('deflate', explode(',', str_replace(' ', '',$this->header('Accept-Encoding'))));
    }

    function getQuery($key = null)
    {
        if ($key == null)
            return static::$query_arr;
        else 
             return static::$query_arr[$key];   
    }    

    // getter destructivo sobre $query_arr
    function shift($key, $default_value = NULL)
    {
        $out = static::$query_arr[$key] ?? $default_value;
        unset(static::$query_arr[$key]);
        return $out;
    }

    function getParam($index){
        return static::$params[$index];
    } 

    function raw()
    {
        return static::$raw;
    }

    function getBody($assoc = true)
    {
        return json_decode(static::$raw, $assoc);
    }

    function getBodyParam($key){
        return $this->getBody()[$key];
    }

    function getCode(){
        return http_response_code();
    }

    /*  ArrayAccess       */

    function offsetSet($offset, $value) {
        if (is_null($offset)) {
            static::$params[] = $value;
        } else {
            static::$params[$offset] = $value;
        }
    }

    function offsetExists($offset) {
        return isset(static::$params[$offset]);
    }

    function offsetUnset($offset) {
        unset(static::$params[$offset]);
    }

    function offsetGet($offset) {
        return isset(static::$params[$offset]) ? static::$params[$offset] : null;
    }

    /* Arrayable Interface */ 

    function toArray(){
        return static::$params;
    }

}