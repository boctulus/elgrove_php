<?php

namespace simplerest\controllers;

global $api_version;

if (!file_exists(CORE_PATH . 'api'. DIRECTORY_SEPARATOR . $api_version . DIRECTORY_SEPARATOR . 'ApiController.php')){  
    \simplerest\core\Response::getInstance()->sendError("API version $api_version is not supported", 400);
}

class_alias("\simplerest\core\api\\$api_version\ApiController", 'simplerest\controllers\ApiController');


class MyApiController extends ApiController
{
    static protected $folder_field;
    static protected $guest_access;
        
    // ACL   
    protected $scope = [
        'guest'      => [],  
        'registered' => [],
        'basic'      => ['read'],
        'regular'    => ['read', 'write']
    ];

    function __construct()
    {
        // CORS
        $headers = [
            'access-control-allow-Origin' => '*'
        ];
   
        $auth = new \simplerest\controllers\AuthController();
        parent::__construct($headers, $auth);
    }

}