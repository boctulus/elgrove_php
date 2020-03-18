<?php

namespace simplerest\controllers;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;

class MakeController extends Controller
{   
	
    function __construct()
    {
        parent::__construct();
    }

    // php index.php make
    function index(){
        return 'Faltan opciones!';
    }

    
	
}
