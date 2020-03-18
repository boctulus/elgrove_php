<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController;; 

class OtherPermissions extends MyApiController
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
