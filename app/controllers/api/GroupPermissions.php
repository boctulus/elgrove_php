<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;

class GroupPermissions extends MyApiController
{     
    protected $scope = [
        'guest'   => [ ],  
        'basic'   => ['read'],
        'regular' => ['read', 'write']
    ];
    
    function __construct()
    {
        parent::__construct();
    }
        
} // end class
