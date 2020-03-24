<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Users extends MyApiController
{
    //static protected $owned = false;
	static protected $guest_access = true;

    protected $scope = [
        'guest'         => ['read'], 
        'registered'    => ['read'],
        'copropietario' => ['read']
    ];

    function __construct()
    {
        parent::__construct();
    }
        
} // end class
