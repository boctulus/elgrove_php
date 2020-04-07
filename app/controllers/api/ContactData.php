<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\Factory;

class ContactData extends MyApiController
{
    //static protected $owned = false;
	static protected $guest_access = true;

    protected $scope = [
        'guest'         => ['read'], 
        'registered'    => ['read'],
        'copropietario' => ['read']
    ];

    function post (){
        Factory::response()->sendError('Not implemented', 501);
	}
	
	function delete ($id = null){
        Factory::response()->sendError('Not implemented', 501);
    }

    function __construct()
    {
        parent::__construct();
    }
        
} // end class
