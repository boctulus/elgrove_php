<?php

namespace simplerest\core\api\v1;

use simplerest\core\Controller;
use simplerest\core\interfaces\IAuth;
use simplerest\libs\Factory;
use simplerest\libs\Arrays;
use simplerest\libs\DB;
use simplerest\libs\Debug;
use simplerest\libs\Url;
use simplerest\libs\Validator;
use simplerest\models\RolesModel;
use simplerest\core\exceptions\InvalidValidationException;

class MySelf extends Controller 
{ 
    protected $modelName = 'UsersModel';
    
    function __construct(array $headers = []) 
    {   
        header('Access-Control-Allow-Origin: *');    
        header('Access-Control-Allow-Headers: Authorization,Content-Type'); 
        header('Access-Control-Allow-Credentials: True');        
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            Factory::response()->sendCode(200);
        }
         
        $this->config = include CONFIG_PATH . 'config.php';

        $auth_object = new \simplerest\core\api\v1\AuthController();

        if ($this->config['debug_mode'] == false)
            set_exception_handler([$this, 'exception_handler']);

        $operations = [ 
            'read'   => ['get'],
            'create' => ['post'],
            'update' => ['put', 'patch'],
            'delete' => ['delete'],
            'write'  => ['post', 'put', 'patch', 'delete']
        ];           

        $this->auth_payload = $auth_object->check();

        if (!empty($this->auth_payload)){
            $this->uid = $this->auth_payload->uid; 
            //Debug::dd($this->uid, 'UID:');

            $r = new RolesModel();
            $this->roles  = $this->auth_payload->roles;              

            $this->is_admin = false;
            foreach ($this->roles as $role){
                if ($r->is_admin($role)){                    
                    $this->is_admin = true;
                    break;
                }
            }            
        }else{
            $this->uid = null;
            $this->is_admin = false;
            $this->roles = ['guest'];
        }
        
        //var_export($this->roles);
        //exit;

        // y si ya se que es admin....
        if ($this->is_admin){
            $this->callable = ['get', 'post', 'put', 'patch', 'delete'];
        }else{
            foreach ($this->roles as $role){
                if ($role == NULL)
                    continue;

                if (isset($this->scope[$role])){
                    $cruds = $this->scope[$role];

                    if (!empty($this->scope[$role])){
                        foreach ($operations as $op => $verbs) {
                            if (in_array($op, $cruds))
                                $this->callable = array_merge($this->callable, $verbs);
                        }
                    } 
                }                    
            }    
        }

        //var_export($this->callable);

        if (empty($this->callable))
            Factory::response()->sendError('You are not authorized',403);

        $this->callable = array_merge($this->callable,['head','options']);

        // aditional headers

        $headers = array_merge($headers, ['Access-Control-Allow-Methods' => implode(',',array_map( function ($e){ return strtoupper($e); },$this->callable )) ]);
        $this->setheaders($headers);
    }

    
    /**
     * setheaders
     * mover a Response *
     *
     * @param  mixed $headers
     *
     * @return void
     */
    private function setheaders(array $headers = []) {
       // $headers = array_merge($this->default_headers, $headers); 
       
        foreach ($headers as $k => $val){
            if (empty($val))
                continue;
            
            header("${k}:$val");
        }
    }

    /**
     * exception_handler
     *
     * @param  mixed $e
     *
     * @return void
     */
    function exception_handler($e) {
        Factory::response()->sendError($e->getMessage());
    }

    
    /**
     * head
     * discard conentent (body)
     * 
     * @param  mixed $id
     *
     * @return void
     */
    function head(int $id = null) {
        if (method_exists($this,'get')){
            ob_start();
            $this->get($id);
            ob_end_clean();
        }
    }

    /**
     * options
     *
     * @return void
     */
    function options(){
        // This function is bypassed in constructor
    }

    
    /**
     * get
     *
     * @param  mixed $id
     *
     * @return void
     */
    function get(){
        try {
            $conn = DB::getConnection();

            $model    = 'simplerest\\models\\'.$this->modelName;
            $instance = (new $model($conn))->setFetchMode('ASSOC'); 
           
            $user = $instance->where(['id', $this->uid])->first();

            Factory::response()->code(200)->send($user);        

        } catch (\Exception $e) {            
            Factory::response()->sendError($e->getMessage());
        }	    
    } //    
    
    protected function modify($id = NULL, bool $put_mode = false)
    { 
        if ($id == null)
            Factory::response()->sendError("Lacks id in request",400);

        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);
        
        try {
            $model    = 'simplerest\\models\\'.$this->modelName;
            
            $conn = DB::getConnection();            

            // Creo una instancia
            $instance = new $model();
            $instance->setConn($conn)->setFetchMode('ASSOC');

            foreach ($data as $k => $v){
                if (strtoupper($v) == 'NULL' && $instance->isNullable($k)) 
                    $data[$k] = NULL;
            }

            $validado = (new Validator())->setRequired($put_mode)->validate($instance->getRules(), $data);
            if ($validado !== true){
                Factory::response()->sendError('Data validation error', 400, $validado);
            }
    
            if ($instance->where(['id', $id])->update($data) !== false)
                Factory::response()->sendJson("OK");
            else
                Factory::response()->sendError("Error in PATCH",404);	

        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (\Exception $e) {
            Factory::response()->sendError("Error during PATCH for id=$id with message: {$e->getMessage()}");
        }
    } //

        
    /**
     * put
     *
     * @param  int $id
     *
     * @return void
     */
    function put(){
        $id = $this->uid;
        $this->modify($id, true);
    } // 
    

    /**
     * patch
     *
     * @param  mixed $id
     *
     * @return void
     */
    function patch($id = NULL)
    { 
        $id = $this->uid;
        $this->modify($id);
    } //

        
    /**
     * delete
     *
     * @param  mixed $id
     *
     * @return void
     */
    function delete(){
        $id = $this->uid;
       
        $data = Factory::request()->getBody();        
        $folder = $data['folder'] ?? null;

        try {    
            $conn = DB::getConnection();
        
            $model    = 'simplerest\\models\\'.$this->modelName;
            $instance = new $model($conn);

            if (!$instance->where(['id', $id])->exists()){
                Factory::response()->code(404)->sendError("User for id=$id does not exists");
            }

            $instance->fill(['deleted_at']); 
            
            if($instance->delete(true)){
                Factory::response()->sendJson("OK");
            }	
            else
                Factory::response()->sendError("User not found",404);

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during DELETE for id=$id with message: {$e->getMessage()}");
        }

    } // 
       
    
}  