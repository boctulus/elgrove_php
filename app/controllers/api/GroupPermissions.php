<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;

class GroupPermissions extends MyApiController
{     
    protected $scope = [
        'guest'   => [ ],  
        'copropietario'   => ['read']
    ];
    
    function __construct()
    {
        parent::__construct();
    }
        
} // end class
