<?php

namespace simplerest\controllers\api;

global $api_version;

if (!file_exists(CORE_PATH . 'api'. DIRECTORY_SEPARATOR . $api_version . DIRECTORY_SEPARATOR . 'ApiController.php')){  
    \simplerest\core\Response::getInstance()->sendError("API version $api_version is not supported", 400);
}

class_alias("\simplerest\core\api\\$api_version\MySelf", 'simplerest\controllers\api\Myself');

class Me extends MySelf
{ 
    protected $scope = [
        'guest'          => [], 
        'registered'     => ['read'],
        'copropietario'  => ['read']
    ];    
}  