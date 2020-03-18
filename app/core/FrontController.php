<?php

namespace simplerest\core;

use simplerest\libs\Url;
use simplerest\libs\Arrays;
use simplerest\libs\Debug;

class FrontController
{
    const DEFAULT_ACTION = "index";

    static function resolve()
    {
        global $argv;
        global $api_version;

        $config = include '../config/config.php';

        if (php_sapi_name() != 'cli'){
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $path = preg_replace('/(.*)\/index.php/', '/', $path);
    
            if ($config['BASE_URL'] != '/' && strpos($path, $config['BASE_URL']) === 0) {
                $path = substr($path, strlen($config['BASE_URL']));
            }   
    
            if ($path === false || ! Url::url_check($_SERVER['REQUEST_URI']) )
                Response::getInstance()->sendError('Malformed url', 400); 
    
            $_params = explode('/', $path);
    
            if (empty($_params[0]))  
                array_shift($_params);
        }else{
            $_params = array_slice($argv, 1);
        }
        
        $req = Request::getInstance();            
    
        if ($_params[0]=='api'){
            if (!isset($_params[1]))
                Response::getInstance()->sendError('API version is missing');

            if (!preg_match('/^v[0-9]+(\.+[0-9]+)?$/', $_params[1], $matches) )
                Response::getInstance()->sendError("Incorrect format for API version");

            $api_version = $_params[1]; 
            
            $controller = $_params[2];
            $params = array_slice($_params,3,2);
            $req->setParams($params);    
           
            // CamelCase to came_case
            $controller = implode('',array_map('ucfirst',explode('_',$controller)));
           
            if ($controller == 'trash_can' || $controller == 'trashCan' || $controller == 'TrashCan')
                $namespace = 'simplerest\\core\\api\\'. $api_version . '\\';
            else
                $namespace = 'simplerest\\controllers\\api\\';

            $class_name = $namespace . ucfirst($controller); //
  
            ///
            $asked_method = NULL;
            if ($config['method_override']['by_url']){
                $asked_method  =  $req->shift('_method');
            }

            if ($asked_method == NULL && $config['method_override']['by_header']){
                $asked_method  =  $req->header('X-HTTP-Method-Override'); 
            }

            $method = $asked_method != NULL ? strtolower($asked_method) : strtolower($_SERVER['REQUEST_METHOD']);
            ///
        }else{
            //Debug::dd($_params, 'PARAMS:');

            $namespace = 'simplerest\\controllers\\';

            if (empty($_params) || $_params[0]==''){
                $class_file = substr($config['DEFAULT_CONTROLLER'],0, strlen($config['DEFAULT_CONTROLLER'])-10);
                $class_name = ucfirst($class_file);
                $class_name = "${namespace}${class_name}Controller";
                $method = self::DEFAULT_ACTION;  
                $params = [];      
            }else{
                // Hipótesis
                $ix = 0;
                $folder = '';
                $controller = $_params[$ix];

                $class_file =  CONTROLLERS_PATH.ucfirst($controller).'Controller.php';
                $cnt  = count($_params) -1;
                while (!file_exists($class_file) && ($ix < $cnt)){
                    $ix++;
                    $folder = implode(DIRECTORY_SEPARATOR, array_slice($_params,0,$ix)). DIRECTORY_SEPARATOR;
                    //Debug::dd($folder, 'NAMESPACE:');
                    $controller = $_params[$ix];
                    $class_file =  CONTROLLERS_PATH. $folder. ucfirst($controller).'Controller.php';
                    //Debug::dd($class_file, "Probando ...");
                }

                //Debug::dd($class_file, "Probando ...");
                
                $action = $_params[$ix+1] ?? null;
                $params = array_slice($_params,$ix+2);
                $req->setParams($params);                    

                $method = !empty($action) ? $action : self::DEFAULT_ACTION;
        
                $class_name = ucfirst($controller);
                $class_name = "${namespace}${folder}${class_name}Controller";

                //Debug::dd($class_name, 'CLASS_NAME:');
                //Debug::dd($method, 'METHOD:');
            }
            
        }
        
       
        /*
            Considerar usar Accept-Language en los headers en su lugar.

            Accept-Language is the opening bid, offering a set of options; Content-Language is the resolution.
        */

        // i18n
        $lang = $req->shift('lang');
        
        if ($lang != NULL)
            setlocale(LC_ALL, "$lang.UTF-8");


        if (!class_exists($class_name))
            Response::getInstance()->sendError("Internal error - controller class $class_name not loaded", 500);  

        if (!method_exists($class_name, $method))
            Response::getInstance()->sendError("Internal error - method $method does not exist in $class_name", 500); 
                
        $controller_obj = new $class_name();

        // Only for API Rest
        if ($_params[0]=='api'){
            if (!in_array($method, $controller_obj->getCallable())){
                Response::getInstance()->send("Not authorized for $controller:$method", 403);
            }
        }

        $data = call_user_func_array([$controller_obj, $method], $params);
        Response::getInstance()->send($data);
        exit;
    }
}

