<?php

if (!defined('HTTP_PROTOCOL'))
    define('HTTP_PROTOCOL', 'http');

////////////////////////////////////

if (!defined('ROOT_PATH'))
    define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR );

if (!defined('CONFIG_PATH'))
	define('CONFIG_PATH', ROOT_PATH  . 'config' . DIRECTORY_SEPARATOR);

if (!defined('UPLOADS_PATH'))
    define('UPLOADS_PATH', ROOT_PATH . 'uploads'. DIRECTORY_SEPARATOR); 

if (!defined('LOGS_PATH'))
    define('LOGS_PATH', ROOT_PATH . 'logs'. DIRECTORY_SEPARATOR); 

if (!defined('VENDOR_PATH'))
    define('VENDOR_PATH', ROOT_PATH . 'vendor'. DIRECTORY_SEPARATOR); 


if (!defined('APP_PATH'))
    define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR);

if (!defined('CORE_PATH'))
    define('CORE_PATH', APP_PATH . 'core'. DIRECTORY_SEPARATOR);
    
if (!defined('MODELS_PATH'))
    define('MODELS_PATH', APP_PATH . 'models'. DIRECTORY_SEPARATOR);    

if (!defined('VIEWS_PATH'))
    define('VIEWS_PATH', APP_PATH .  'views' . DIRECTORY_SEPARATOR);  

if (!defined('CONTROLLERS_PATH'))
    define('CONTROLLERS_PATH', APP_PATH . 'controllers' . DIRECTORY_SEPARATOR);    

if (!defined('API_PATH'))
    define('API_PATH', APP_PATH  . 'api' . DIRECTORY_SEPARATOR); 

if (!defined('LIBS_PATH'))
    define('LIBS_PATH', APP_PATH . 'libs' . DIRECTORY_SEPARATOR);   

if (!defined('HELPERS_PATH'))
    define('HELPERS_PATH', APP_PATH . 'helpers' . DIRECTORY_SEPARATOR);  

if (!defined('LOCALE_PATH'))
    define('LOCALE_PATH', APP_PATH . 'locale' . DIRECTORY_SEPARATOR);  


if (!defined('PUBLIC_PATH'))
    define('PUBLIC_PATH', ROOT_PATH . 'public' . DIRECTORY_SEPARATOR); 

if (!defined('ASSETS_PATH'))
    define('ASSETS_PATH', PUBLIC_PATH . 'assets' . DIRECTORY_SEPARATOR); 

if (!defined('IMAGES_PATH'))
    define('IMAGES_PATH', PUBLIC_PATH . 'images' . DIRECTORY_SEPARATOR);     

if (!defined('CSS_PATH'))
    define('CSS_PATH', PUBLIC_PATH . 'images' . DIRECTORY_SEPARATOR);   

if (!defined('JS_PATH'))
    define('JS_PATH', PUBLIC_PATH . 'js' . DIRECTORY_SEPARATOR);       