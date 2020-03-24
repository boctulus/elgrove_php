<?php

namespace simplerest\core\api\v1;

use simplerest\core\ResourceController;
use simplerest\core\interfaces\IAuth;
use simplerest\libs\Factory;
use simplerest\libs\Arrays;
use simplerest\libs\DB;
use simplerest\libs\Debug;
use simplerest\libs\Url;
use simplerest\models\GroupPermissionsModel;
use simplerest\models\OtherPermissionsModel;
use simplerest\models\FoldersModel;
use simplerest\models\RolesModel;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;


abstract class ApiController extends ResourceController
{
    static protected $guest_access = false;  
    static protected $owned = true;
    static protected $folder_field;
    static protected $soft_delete = true;

    protected $scope;
    protected $callable = [];
    protected $config;
    protected $modelName;
    protected $model_table;
    

    function __construct(array $headers = [], IAuth $auth_object = null) 
    {   
        parent::__construct();

        $this->config = include CONFIG_PATH . 'config.php';

        if ($this->config['debug_mode'] == false)
            set_exception_handler([$this, 'exception_handler']);
        
        if (preg_match('/([A-Z][a-z0-9_]+[A-Z]*[a-z0-9_]*[A-Z]*[a-z0-9_]*[A-Z]*[a-z0-9_]*)/', get_called_class(), $matchs)){
            $this->modelName = $matchs[1] . 'Model';
            $this->model_table = strtolower($matchs[1]);
        }   

        $perms = $this->getPermissions($this->model_table);
        //var_export($perms); exit; ///

        $operations = [ 
            'read'   => ['get'],
            'create' => ['post'],
            'update' => ['put', 'patch'],
            'delete' => ['delete'],
            'write'  => ['post', 'put', 'patch', 'delete']
        ];           

        //var_export(['is_admin' => $this->is_admin]); ///
        //exit;

        // y si ya se que es admin....
        if ($this->isAdmin()){
            $this->callable = ['get', 'post', 'put', 'patch', 'delete'];
        }else{
            if ($perms !== NULL){

                $create = ($perms & 8) AND 1;
                $read   = ($perms & 4) AND 1; 
                $update = ($perms & 2) AND 1; 
                $delete = ($perms & 1) AND 1;

                // individual permissions *replaces* role permissions

                if ($create)
                    $this->callable = array_merge($this->callable, $operations['create']); 

                if ($read)
                    $this->callable = array_merge($this->callable, $operations['read']);   
                    
                if ($update)
                    $this->callable = array_merge($this->callable, $operations['update']); 

                if ($delete)
                    $this->callable = array_merge($this->callable, $operations['delete']); 
            }else{
                foreach ($this->roles as $role){
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
        }

        //var_export($this->is_admin);
        //var_dump(['perms' => $perms]);
        //var_export($this->scope);
        //var_export($cruds);
        //var_export($this->callable);
        //exit;


        //echo json_encode($this->is_admin);
        //exit;

        if (empty($this->callable)){
            Factory::response()->sendError('You are not authorized!',403);
        }
            

        $this->callable = array_merge($this->callable,['head','options']);

        // aditional headers

        //$headers = array_merge($headers, ['Access-Control-Allow-Methods' => implode(',',array_map( function ($e){   return strtoupper($e); },$this->callable )) ]);
        //Factory::response()->sendHeaders(array_merge($this->default_headers, $headers));
    }

    static function get_owned(){
        return static::$owned;
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
    function options() {
    }
 
    /**
     * hasPerm
     *
     * @param  int    $folder
     * @param  object $conn
     * @param  string $operation
     *
     * @return bool
     */
    protected function hasPerm(int $folder, object $conn, string $operation)
    {
        if ($operation != 'r' && $operation != 'w')
            throw new \InvalidArgumentException("Permissions are 'r' or 'w' but not '$operation'");

        $o = (new OtherPermissionsModel($conn))->setFetchMode('ASSOC');

        $rows = $o->where(['folder_id', $folder])->get();

        $r = $rows[0]['r'] ?? null;
        $w = $rows[0]['w'] ?? null;

        if ($this->isGuest()){
            $r = $r && $rows[0]['guest'];
            $w = $w && $rows[0]['guest'];
        }

        if (($operation == 'r' && $r) || ($operation == 'w' && $w)) {
            return true;
        }
        
        $g = (new GroupPermissionsModel($conn))->setFetchMode('ASSOC');
        $rows = $g->where([
                                    ['folder_id', $folder], 
                                    ['member', $this->uid]
        ])->get();

        $r = $rows[0]['r'] ?? null;
        $w = $rows[0]['w'] ?? null;

        if (($operation == 'r' && $r) || ($operation == 'w' && $w)) {
            return true;
        }

        return false;
    } 
    
    /**
     * get
     *
     * @param  mixed $id
     *
     * @return void
     */
    function get($id = null) {
        global $api_version;

        try {            

            $conn = DB::getConnection();

            $model    = 'simplerest\\models\\'.$this->modelName;
            $instance = (new $model($conn))->setFetchMode('ASSOC'); 
            
            $_get    = Factory::request()->getQuery();
            $data    = [];
            
            foreach (['created_by', 'updated_by', 'deleted_by', 'belongs_to'] as $f){
                if (isset($_get[$f])){
                    if ($_get[$f] == 'me')
                        $_get[$f] = $this->uid;
                    elseif (is_array($_get[$f])){
                        foreach ($_get[$f] as $op => $idx){                            
                            if ($idx == 'me'){
                                $_get[$f][$op] = $this->uid;
                            }else{      
                                $p = explode(',',$idx);
                                if (count($p)>1){
                                foreach ($p as $ix => $idy){
                                    if ($idy == 'me')
                                        $p[$ix] = $this->uid;
                                    }
                                }
                                $_get[$f][$op] = implode(',',$p);
                            }
                        }
                    }else{
                        $p = explode(',',$_get[$f]);
                        if (count($p)>1){
                        foreach ($p as $ix => $idx){
                            if ($idx == 'me')
                                $p[$ix] = $this->uid;
                            }
                        }
                        $_get[$f] = implode(',',$p);
                    }
                }
            }
    
            //var_export($_get);
            //exit; ////

            if (isset($_get['created_by']) && $_get['created_by'] == 'me')
                $_get['created_by'] = $this->uid;

            foreach ($_get as $f => $v){
                if (!is_array($v) && strpos($v, ',')=== false)
                    $data[$f] = $v;
            }     

            //var_export($_get);
            //exit;
                
            $owned = static::get_owned() && $instance->inSchema(['belongs_to']);

            // patch for Nginx
            $_q      = Arrays::shift($_get,'q');
            
            $fields  = Arrays::shift($_get,'fields');
            $fields  = $fields != NULL ? explode(',',$fields) : NULL;

            $properties = $instance->getProperties();
            
            foreach ((array) $fields as $field){
                if (!in_array($field,$properties))
                    Factory::response()->sendError("Unknown field '$field'", 400);
            }

            $exclude = Arrays::shift($_get,'exclude');
            $exclude = $exclude != NULL ? explode(',',$exclude) : NULL;

            foreach ((array) $exclude as $field){
                if (!in_array($field,$properties))
                    Factory::response()->sendError("Unknown field '$field' in exclude", 400);
            }

            $ignored = [];

            if ($exclude != null)
                $instance->hide($exclude);
                       
            $pretty  = Arrays::shift($_get,'pretty');
            $folder  = Arrays::shift($_get,'folder');

            foreach ($_get as $key => $val){
                if ($val == 'NULL' || $val == 'null'){
                    $_get[$key] = NULL;
                }               
            }

            //var_dump($_get);
            //exit;
        
            if ($folder !== null)
            {
                $f = DB::table('folders')->setFetchMode('ASSOC');
                $f_rows = $f->where(['id' => $folder])->get();
        
                if (count($f_rows) == 0 || $f_rows[0]['resource_table'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404);  
        
                $folder_access = $f_rows[0]['belongs_to'] == $this->uid  || $this->hasPerm($folder, $conn, 'r');    
                if (!$folder_access)
                    Factory::response()->sendError("You don't have permission for the folder $folder", 403);
            }

            if ($id != null)
            {
                $_get = [
                    ['id', $id]
                ];  

                if (empty($folder)){               
                    // root, by id
                    if (!$this->is_admin && $owned)
                        $_get[] = ['belongs_to', $this->uid];
                }else{
                    // folder, by id
                    if (empty(static::$folder_field))
                        Factory::response()->sendError("folder_field is undefined", 403);
                    
                    if ($this->isGuest() && !$folder_access)
                        Factory::response()->send([]);    
                        
                    $_get[] = [static::$folder_field, $f_rows[0]['name']];
                    $_get[] = ['belongs_to', $f_rows[0]['belongs_to']];
                }

                $rows = $instance->where($_get)->get($fields); 
                if (empty($rows))
                    Factory::response()->sendError('Not found', 404);
                else
                    Factory::response()->send($rows[0]);
            }else{    
                // "list
                
                $props    = Arrays::shift($_get,'props');
                $group_by = Arrays::shift($_get,'groupBy');
                $having   = Arrays::shift($_get,'having');
             
                $get_limit = function(&$limit){
                    if ($limit == NULL)
                        $limit = min($this->config['paginator']['max_limit'], $this->config['paginator']['default_limit']);
                    else{
                        if ($limit !=0)
                            $limit = min($limit, $this->config['paginator']['max_limit']);
                        else    
                            $limit = $this->config['paginator']['max_limit'];
                    } 
                };
   
                $page  = Arrays::shift($_get,'page');
                $page_size = Arrays::shift($_get,'pageSize');

                if ($page != null)
                    $page = (int) $page;

                if ($page_size  != null)
                    $page_size  = (int) $page_size;

                if ($page != NULL || $page_size != NULL){
                    $get_limit($page_size);

                    if ($page == NULL)
                        $page = 1;

                    $limit  = $page_size;
                    $offset = $page_size * ($page -1);

                    //var_export(['limit' =>$limit, 'offset' => $offset]);
                }else{
                    $limit  = Arrays::shift($_get,'limit');
                    $offset = Arrays::shift($_get,'offset',0);                    

                    $get_limit($limit);
                    $page_size = $limit;
                }

                $order  = Arrays::shift($_get,'order');

                // Importante:
                $_get = Arrays::nonassoc($_get);

                $allops = ['eq', 'gt', 'gteq', 'lteq', 'lt', 'neq'];
                $eqops  = ['=',  '>' , '>=',   '<=',   '<',  '!=' ];

                //var_export($_get);

                foreach ($_get as $key => $val){
                    if (is_array($val)){

                        $campo = $val[0];                       

                        if (is_array($val[1])){                             

                            #var_export($val[1]); ///

                            foreach ($val[1] as $op => $v){

                                #var_export([$op, $v]); ///
                                 
                                switch ($op) {
                                    case 'contains':
                                        $_get[$key] = [$campo, '%'.$v.'%', 'like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'notContains':
                                        $_get[$key] = [$campo, '%'.$v.'%', 'not like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'startsWith':
                                        $_get[$key] = [$campo, $v.'%', 'like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'notStartsWith':
                                        $_get[$key] = [$campo, $v.'%', 'not like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'endsWith':
                                        $_get[$key] = [$campo, '%'.$v, 'like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'notEndsWith':
                                        $_get[$key] = [$campo, '%'.$v, 'not like'];
                                        $ignored[] = $campo;
                                        $data[$campo][] = $v;
                                    break;
                                    case 'in':                                    
                                        if (strpos($v, ',')!== false){    
                                            $vals = explode(',', $v);
                                            $_get[$key] = [$campo, $vals, 'IN']; 

                                            foreach ($vals as $_v){
                                                $data[$campo][] = $_v;
                                            }
                                        }                                         
                                    break;
                                    case 'notIn':
                                        if (strpos($v, ',')!== false){    
                                            $vals = explode(',', $v);
                                            $_get[$key] = [$campo, $vals, 'NOT IN'];

                                            foreach ($vals as $_v){
                                                $data[$campo][] = $_v;
                                            }
                                        }                                         
                                    break;
                                    case 'between':
                                        if (substr_count($v, ',') == 1){    
                                            $vals = explode(',', $v);
                                            unset($_get[$key]);

                                            $min = min($vals[0],$vals[1]);
                                            $max = max($vals[0],$vals[1]);

                                            $_get[] = [$campo, $min, '>='];
                                            $_get[] = [$campo, $max, '<='];

                                            $data[$campo][] = $min;
                                            $data[$campo][] = $max;
                                        }                                         
                                    break;
                                    default:
                                        // 'eq', 'gt', ...

                                        $found = false;
                                        foreach ($allops as $ko => $oo){
                                            if ($op == $oo){
                                                $op = $eqops[$ko];
                                                unset($_get[$key]);
                                                $_get[] = [$campo, $v, $op];
                                                $data[$campo][] = $v; // 
                                                $found = true;                            
                                                break;                                    
                                            }                                    
                                        }

                                        if (!$found)
                                            Factory::response()->sendError("Invalid operator '$op'", 400);
                                    break;
                                }
                            }
                            
                        }else{                           

                            // IN
                            $v = $val[1];
                            if (strpos($v, ',')!== false){    
                                $vals = explode(',', $v);
                                $_get[$key] = [$campo, $vals];    
                                
                                foreach ($vals as $_v){
                                   $data[$campo][] = $_v;
                                }
                            } 
                        }   
                        
                    }                         
                }
                

                // Si se pide algo que involucra un campo no está en el schema lanzar error
                foreach ($_get as $arr){
                    if (!in_array($arr[0],$properties))
                        Factory::response()->sendError("Unknown field '$arr[0]'", 400);
                }
                  
                if (empty($folder)){
                    // root, sin especificar folder ni id (lista)
                    if ($this->isGuest()){
                        if (!static::$guest_access){
                            Factory::response()->send([]);
                        }
                    }else
                        if (!$this->is_admin && $owned)
                            $_get[] = ['belongs_to', $this->uid];        
                }else{
                    // folder, sin id
                    if (empty(static::$folder_field))
                        Factory::response()->sendError("'folder_field' is undefined", 403);
                  
                    if ($this->isGuest() && !$folder_access)
                        Factory::response()->send([]); 

                    $_get[] = [static::$folder_field, $f_rows[0]['name']];
                    $_get[] = ['belongs_to', $f_rows[0]['belongs_to']];
                }
                
                if ($id == null){
                    $validation = (new Validator())->setRequired(false)->ignoreFields($ignored)->validate($instance->getRules(),$data);
                    //var_export(['data' => $data, 'rules'=> $instance->getRules(), 'validation' => $validation]);

                    if ($validation !== true)
                        throw new InvalidValidationException(json_encode($validation));
                }      
           
                if (strtolower($pretty) == 'false' || $pretty === 0)
                    $pretty = false;
                else
                    $pretty = true;   

                //var_export($_get); ////
                //var_export($_SERVER["QUERY_STRING"]);

                $query = Factory::request()->getQuery();
                
                if (isset($query['offset'])) 
                    unset($query['offset']);

                if (isset($query['limit'])) 
                    unset($query['limit']);

                if (isset($query['page'])) 
                    unset($query['page']);

                if (!isset($query['pageSize'])) 
                    $query['pageSize'] = $page_size;

                
                //////////
    
                // MIN, MAX, SUM, COUNT, AVG
                if (preg_match('/(min|max|sum|avg|count)\(([a-z\*]+)\)( as [a-z]+)?/i', $props, $matches)){
                    $ag_fn = strtolower($matches[1]);
                    $ag_ff = $matches[2];
                
                    if (preg_match('/[a-z]+\([a-z\*]+\) as ([a-z]+)/i', $props, $matches)){
                        $ag_alias = $matches[1];
                    }else
                        $ag_alias = NULL;
                }

                // WHERE
                $instance->where($_get);
                //var_export($_get);

                // GROUP BY
                if ($group_by != NULL){
                    $group_by = explode(',', $group_by);
                    $instance->groupBy($group_by);                   
                }                    

                // HAVING
                if (preg_match('/([a-z]+)\(([a-z\*]+)\)([><=]+)([0-9\.]+)/i', $having, $matches)){
                    $hv_fn = strtoupper($matches[1]);
                    $hv_ff = $matches[2];
                    $hv_op = $matches[3];
                    $hv_vv = $matches[4];                   

                    //var_export($matches);                    
                    $instance->having(["$hv_fn($hv_ff)", $hv_vv, $hv_op]);
                }elseif (preg_match('/([a-z]+)([><=]+)([0-9\.]+)/i', $having, $matches)){
                    $hv_fn_alias = $matches[1];
                    $hv_op = $matches[2];
                    $hv_vv = $matches[3]; 

                    $instance->having([$hv_fn_alias, $hv_vv, $hv_op]);
                }

                // ORDER BY
                if ($order !=  NULL)
                    $instance->orderBy($order);
                
                // LIMIT
                if ($limit != NULL)
                    $instance->limit($limit);

                // OFFSET
                if ($offset != NULL)
                    $instance->offset($offset);

                /*
                    Debe incluir los alias 
                */
                if (!empty($fields))
                    $instance->select($fields);


                if (isset($ag_fn)){
                    $rows = $instance->$ag_fn($ag_ff, $ag_alias);
                }else                               
                    $rows = $instance->get();
                

                //    
                ///Debug::dd($instance->getLastPrecompiledQuery());
                

                $res = Factory::response()->setPretty($pretty)->code(200);

                /*
                    Falta paginar cuando hay groupBy & having
                */

                //  pagino solo sino hay funciones agregativas
                if (!isset($ag_fn)){
                    $total = (int) array_values((new $model($conn))->where($_get)->count())[0];
               
                    $page_count = ceil($total / $limit);

                    if ($page == NULL)
                        $page = ceil($offset / $limit) +1;
                    
                    if ($page +1 <= $page_count){
                        $query['page'] = ($page +1);

                        $next =  Url::protocol() . '//' . $_SERVER['HTTP_HOST'] . '/api/' . $api_version . '/'. $this->model_table . '?' . $query = str_replace(['%5B', '%5D', '%2C'], ['[', ']', ','], http_build_query($query));
                    }else{
                        $next = 'null';
                    }        

                    $pg = [ 
                        'total' => $total,
                        'count' => count($rows),
                        'currentPage' => $page,
                        'totalPages' => $page_count, 
                        'pageSize' => $page_size,
                        'nextUrl' => $next                                              
                    ];  

                    $res->setPaginator($pg);
                }
                                        
                $res->send($rows);       
            }
        
        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (SqlException $e) { 
            Factory::response()->sendError('SQL Exception', 500, json_decode($e->getMessage()));   
        } catch (\Exception $e) {            
            Factory::response()->sendError($e->getMessage());
        }	    
    } // 


    /**
     * post
     *
     * @return void
     */
    function post() {
        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);
        
        $model    = '\\simplerest\\models\\'.$this->modelName;
        $instance = (new $model())->setFetchMode('ASSOC');

        $folder = $data['folder'] ?? null;

        try {
            $conn = DB::getConnection();
            $instance->setConn($conn);

            if ($instance->inSchema(['belongs_to'])){
                if ($this->is_admin){
                    if (!isset($data['belongs_to']))
                        $data['belongs_to'] = $this->uid;
                }elseif (!$this->isGuest())
                    $data['belongs_to'] = $this->uid; 
            }   

            if ($instance->inSchema(['created_by'])){
                $data['created_by'] = $this->uid;
            }

            if ($this->is_admin){               
                $instance->fillAll();
            }
            
            if ($folder !== null)
            {
                if (empty(static::$folder_field))
                    Factory::response()->sendError("'folder_field' is undefined", 403);

                $f = DB::table('folders');
                $f_rows = $f->where(['id' => $folder])->get();
                      
                if (count($f_rows) == 0 || $f_rows[0]['resource_table'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404); 
        
                if ($f_rows[0]['belongs_to'] != $this->uid  && !$this->hasPerm($folder, $conn, 'w'))
                    Factory::response()->sendError("You have not permission for the folder $folder", 403);

                unset($data['folder']);    
                $data[static::$folder_field] = $f_rows[0]['name'];
                $data['belongs_to'] = $f_rows[0]['belongs_to'];    
            }    

            $validado = (new Validator)->validate($instance->getRules(), $data);
            if ($validado !== true){
                Factory::response()->sendError('Data validation error', 400, $validado);
            }  

            if ($instance->create($data)!==false){
                //Debug::dd($instance->getLog());
                Factory::response()->send(['id' => $instance->id], 201);
            }	
            else
                Factory::response()->sendError("Error: creation of resource fails!");

        } catch (InvalidValidationException $e) { 
            Factory::response()->sendError('Validation Error', 400, json_decode($e->getMessage()));
        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage());
        }	

    } // 
    
    protected function modify($id = NULL, bool $put_mode = false)
    { 
        //Factory::response()->send("OK");
        ////

        if ($id == null)
            Factory::response()->sendError("Lacks id in request",400);

        if (!ctype_digit($id))
            Factory::response()->sendError('Bad request', 400, 'Id should be an integer');

        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);
        
        $folder = $data['folder'] ?? null; 

        try {
            $model    = 'simplerest\\models\\'.$this->modelName;            
            $conn = DB::getConnection();       
			
			if (!$this->is_admin){
                $instance0 = (new $model($conn))->setFetchMode('ASSOC');
                $row = $instance0->where(['id', $id])->first();

                if (isset($row['locked']) && $row['locked'] == 1)
                    Factory::response()->sendError("Locked by Admin", 403);
            }

            // Creo una instancia
            $instance = new $model();
            $instance->setConn($conn)->setFetchMode('ASSOC');

            $owned = static::get_owned() && $instance->inSchema(['belongs_to']);

            if (!$this->is_admin){
                if (isset($data['belongs_to']))
                    unset($data['belongs_to']);

                if (isset($data['deleted_at']))
                    unset($data['deleted_at']);
            }else{
                //$instance->fill(['deleted_at']);
                $instance->fillAll();
            }

            if ($folder !== null)
            {
                if (empty(static::$folder_field))
                    Factory::response()->sendError("'folder_field' is undefined", 403);

                $f = DB::table('folders')->setFetchMode('ASSOC');
                $f_rows = $f->where(['id' => $folder])->get();
                      
                if (count($f_rows) == 0 || $f_rows[0]['resource_table'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404); 
        
                if ($f_rows[0]['belongs_to'] != $this->uid  && !$this->hasPerm($folder, $conn, 'w'))
                    Factory::response()->sendError("You have not permission for the folder $folder", 403);

                $folder_name = $f_rows[0]['name'];

                // Creo otra nueva instancia
                $instance2 = new $model();
                $instance2->setConn($conn)->setFetchMode('ASSOC');

                if (count($instance2->where(['id' => $id, static::$folder_field => $folder_name])->get()) == 0)
                    Factory::response()->code(404)->sendError("Register for id=$id does not exists");

                unset($data['folder']);    
                $data[static::$folder_field] = $f_rows[0]['name'];
                $data['belongs_to'] = $f_rows[0]['belongs_to'];    
            } else {

                $instance2 = new $model();
                $instance2->setConn($conn)->setFetchMode('ASSOC'); 

                $rows = $instance2->where(['id' => $id])->get();

                if (count($rows) == 0){
                    Factory::response()->code(404)->sendError("Register for id=$id does not exists");
                }

                if  ($owned && !$this->is_admin && $rows[0]['belongs_to'] != $this->uid)
                    Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
                
            }        

            foreach ($data as $k => $v){
                if (strtoupper($v) == 'NULL' && $instance->isNullable($k)) 
                    $data[$k] = NULL;
            }

            $validado = (new Validator())->setRequired($put_mode)->validate($instance->getRules(), $data);
            if ($validado !== true){
                Factory::response()->sendError('Data validation error', 400, $validado);
            }
    
            if ($instance->inSchema(['updated_by'])){
                $data['updated_by'] = $this->uid;
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
    function put($id = null) {
        $this->modify($id, true);
    } // 
    

    /**
     * patch
     *
     * @param  mixed $id
     *
     * @return void
     */
    function patch($id = NULL) { 
        $this->modify($id);
    } //

        
    /**
     * delete
     *
     * @param  mixed $id
     *
     * @return void
     */
    function delete($id = NULL) {
        if($id == NULL)
            Factory::response()->sendError("Lacks id in request", 400);

        if (!ctype_digit($id))
            Factory::response()->sendError('Bad request', 400, 'Id should be an integer');

        $data = Factory::request()->getBody();        
        $folder = $data['folder'] ?? null;

        try {    
            $conn = DB::getConnection();
        
            $model    = 'simplerest\\models\\'.$this->modelName;
            
            $instance = (new $model($conn))->setFetchMode('ASSOC');
            $instance->fill(['deleted_at']); //

            $owned = static::get_owned() && $instance->inSchema(['belongs_to']);

            $rows = $instance->where(['id', $id])->get();
            
            if (count($rows) == 0){
                Factory::response()->code(404)->sendError("Register for id=$id does not exists");
            }

            if ($folder !== null)
            {
                if (empty(static::$folder_field))
                    Factory::response()->sendError("'folder_field' is undefined", 403);

                $f = DB::table('folders')->setFetchMode('ASSOC');
                $f_rows = $f->where(['id' => $folder])->get();
                      
                if (count($f_rows) == 0 || $f_rows[0]['resource_table'] != $this->model_table)
                    Factory::response()->sendError('Folder not found', 404); 
        
                if ($f_rows[0]['belongs_to'] != $this->uid  && !$this->hasPerm($folder, $conn, 'w'))
                    Factory::response()->sendError("You have not permission for the folder $folder", 403);

                $folder_name = $f_rows[0]['name'];

                // Creo otra nueva instancia
                $instance2 = new $model();
                $instance2->setConn($conn)->setFetchMode('ASSOC');

                if (count($instance2->where(['id' => $id, static::$folder_field => $folder_name])->get()) == 0)
                    Factory::response()->code(404)->sendError("Register for id=$id does not exists");

                unset($data['folder']);    
                $data[static::$folder_field] = $f_rows[0]['name'];
                $data['belongs_to'] = $f_rows[0]['belongs_to'];    
            } else {
                if ($owned && !$this->is_admin && $rows[0]['belongs_to'] != $this->uid){
                    Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
                }
            }  

            $extra = [];

            if ($this->is_admin){
                if ($instance->inSchema(['locked'])){
                    $extra = array_merge($extra, ['locked' => 1]);
                }   
            }else {
                if (isset($rows[0]['locked']) && $rows[0]['locked'] == 1){
                    Factory::response()->sendError("Locked by Admin", 403);
                }
            }

            if ($instance->inSchema(['deleted_by'])){
                $extra = array_merge($extra, ['deleted_by' => $this->uid]);
            }               
       
            if($instance->delete(static::$soft_delete && $instance->inSchema(['deleted_at']), $extra)){
                Factory::response()->sendJson("OK");
            }	
            else
                Factory::response()->sendError("Record not found",404);

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during DELETE for id=$id with message: {$e->getMessage()}");
        }

    } // 
       
    
}  