<?php

namespace simplerest\controllers\sub1\a;

use simplerest\core\Controller;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\Debug;

class OpsController extends Controller
{   
	
    function __construct()
    {
        parent::__construct();
    }

    // php index.php sub1 a ops
    function index(){
        return 'INDEX';
    }

    // php index.php sub1 a ops inc 6
    function inc($a){
        return (int) (++$a);
    }

    function add($a, $b){
        $res = (int) $a + (int) $b;
        return  "$a + $b = " . $res;
    }

    function mul(){
        $req = Request::getInstance();
        $res = (int) $req[0] * (int) $req[1];
        return "$req[0] * $req[1] = " . $res;
    }

    function div(){
        $res = (int) @Request::getParam(0) / (int) @Request::getParam(1);
        //
        // hacer un return en vez de un "echo" me habilita a manipular
        // la "respuesta", conviertiendola a JSON por ejemplo 
        //
        return ['result' => $res];
    }
	
}
