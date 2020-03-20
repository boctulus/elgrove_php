<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 

class Slider extends MyApiController
{ 
    //static protected $folder_field = 'workspace';
    //static protected $owned = false;
    static protected $guest_access = true;

    function __construct()
    {       
        $this->scope['guest']         = ['read', 'write'];  ////
        $this->scope['registered']    = ['read'];
        $this->scope['copropietario'] = ['read'];
        parent::__construct();
    }

        
} // end class
