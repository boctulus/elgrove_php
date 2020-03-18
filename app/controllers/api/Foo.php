<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Foo extends MyApiController
{ 
    protected $scope = [
        'guest'      => [],  
        'registered' => ['create'],
        'basic'      => [],
        'regular'    => ['read']
    ];

    function __construct()
    {       
        parent::__construct();
    }

        
} // end class
