<?php

namespace simplerest\libs;

class Url {

    static function protocol(){
        /*
        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https:';
        } else {
            $protocol = 'http:';
        }
        */

        $config = include CONFIG_PATH . 'config.php';
        
        if ($config['HTTPS'] == 1 || strtolower($config['HTTPS']) == 'on'){
            $protocol = 'https:';
        } else {
            $protocol = 'http:';
        }

        return $protocol;
    }

    static function assets($resource){
        /*
        if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
            $protocol = 'https:';
        } else {
            $protocol = 'http:';
        }
        */

        $config = include CONFIG_PATH . 'config.php';

        $public = $config['BASE_URL'] == '/' ? '' : 'public/';
        return  HTTP_PROTOCOL. '//'. $_SERVER['HTTP_HOST'].'/'.$config['BASE_URL']. $public. 'assets/'.$resource;
    }
    
    static function section($view){
        include VIEWS_PATH . $view;
    }

    /**
     * url_check - complement for parse_url
     *
     * @param  string $url
     *
     * @return bool
     */
    static function url_check(string $url){
        $sym = null;
    
        $len = strlen($url);
        for ($i=0; $i<$len; $i++){
            if ($url[$i] == '?'){
                if ($sym == '?' || $sym == '&')
                    return false;
    
                $sym = '?';
            }elseif ($url[$i] == '&'){
                if ($sym === null)
                    return false;
    
                $sym = '&';
            } 
        }
        return true;
    }
}

