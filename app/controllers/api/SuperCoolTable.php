<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class SuperCoolTable extends MyApiController
{ 
    protected $scope = [
        'guest'      => [],  
        'registered' => ['read'],
        'basic'      => ['read', 'write'],
        'regular'    => ['read', 'write']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
