<?php

function assets($resource){
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
    
    $public =  $config['BASE_URL'] == '/' ? '' : 'public';
    return $protocol . '//' . $_SERVER['HTTP_HOST'].$config['BASE_URL']."$public/assets/".$resource;
}

function section($view){
    include VIEWS_PATH . $view;
}