<?php

namespace simplerest\controllers;

use simplerest\core\Controller;

class MyController extends Controller {
    protected $_title;

    function __construct(){
        parent::__construct();        
    }

    function view(string $view_path, array $vars_to_be_passed = null, $layout = 'app_layout.php'){
        $_ctrl = explode('\\',get_class($this));
        $ctrl  = $_ctrl[count($_ctrl)-1];
        $_title = substr($ctrl,0,strlen($ctrl)-10);     
        
        if(!isset($vars_to_be_passed['title'])){
            $vars_to_be_passed['title'] = $_title;
        }

        parent::view($view_path, $vars_to_be_passed, $layout);
   }

}