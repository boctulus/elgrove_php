<?php

namespace simplerest\core\api\v1;

use simplerest\controllers\MyApiController;
use simplerest\libs\Factory;
use simplerest\libs\Arrays;
use simplerest\libs\Strings;
use simplerest\libs\DB;
use simplerest\libs\Debug;
use simplerest\core\exceptions\InvalidValidationException;
use simplerest\libs\Url;


class Collections extends MyApiController
{   
    function __construct()
    {
        $this->scope['guest'] = [];   
        parent::__construct();
    }

     
    function post() {
        $data = Factory::request()->getBody();  

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);

        
        if (empty($data['entity']))
            Factory::response()->sendError('entity is required', 400);    

        if (empty($data['refs']))
            Factory::response()->sendError('refs is required', 400);        

        $entity = $data['entity'];
        $refs   = $data['refs'];

        try {            
            $entity = Strings::toCamelCase($entity);    
           
            $modelName   = ucfirst($entity) . 'Model';
            $model_table = strtolower($entity);

            $model    = 'simplerest\\models\\'. $modelName;
            $api_ctrl = '\simplerest\\controllers\\api\\' . ucfirst($entity);

            if (!class_exists($model))
                Factory::response()->sendError("Entity $entity does not exists", 400);
            
                      
            $id = DB::table('collections')->create([
                'entity' => $model_table,
                'refs' => json_encode($refs),
                'belongs_to' => $this->uid
            ]);

            if ($id !== false){
                Factory::response()->send(['id' => $id], 201);
            }	
            else
                Factory::response()->sendError("Error: creation of collection fails!");

        } catch (\Exception $e) {
            Factory::response()->sendError($e->getMessage());
        }

    } // 

    protected function modify($id = NULL, bool $put_mode = false)
    {
        if ($id == null)
            Factory::response()->code(400)->sendError("Lacks id in request");

        if (!ctype_digit($id))
            Factory::response()->sendError('Bad request', 400, 'Id should be an integer');

        $data = Factory::request()->getBody();

        if (empty($data))
            Factory::response()->sendError('Invalid JSON',400);

        if (empty($data['entity']))
            Factory::response()->sendError('Lacks entity',400);

        try {
            
            if (!empty($data['remove']) && strtolower($data['remove']) == 'true'){
                if (DB::table('collections')->where(['id', $id])->delete(false)){
                    Factory::response()->sendOK();
                } else {
                    Factory::response()->sendError("Colection not found",404);
                }
            } else {
                Factory::response()->send("Nothing to do");
            }

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during update for colection $id with message: {$e->getMessage()}");
        }
    }        

    /**
     * delete
     *
     * @param  mixed $id
     *
     * @return void
     */
    function delete($id = NULL) {
        if($id == NULL)
            Factory::response()->sendError("Lacks id for Collection in request", 400);

        if (!ctype_digit($id))
            Factory::response()->sendError('Bad request', 400, 'Id should be an integer');

        $data = Factory::request()->getBody();  

        try {            

            $row  = DB::table('collections')->where(['id', $id])->first();
            
            if (!$row){
                Factory::response()->code(404)->sendError("Collection for id=$id does not exists");
            }
            
            if (!$this->is_admin && $row['belongs_to'] != $this->uid){
                Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
            }         

            $entity = Strings::toCamelCase($row['entity']);    
           
            $modelName   = ucfirst($entity) . 'Model';
            $model_table = strtolower($entity);

            $model    = 'simplerest\\models\\'. $modelName;
            $api_ctrl = '\simplerest\\controllers\\api\\' . ucfirst($entity);

            if (!class_exists($model))
                Factory::response()->sendError("Entity $entity does not exists", 400);
            
            $conn = DB::getConnection();     
            $instance = (new $model($conn));     

            
            $refs = json_decode($row['refs']);
            $affected = 0;
            DB::transaction(function() use ($instance, $api_ctrl, $refs, $id, &$affected) {
                $affected = $instance->whereIn('id', $refs)->delete($api_ctrl::$soft_delete && $instance->inSchema(['deleted_at']));
                DB::table('collections')->where(['id' => $id])->delete(false);
            });   

            //echo " affected ( $affected )";

            Factory::response()->send(['affected_rows' => $affected]);  

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during DELETE for collection $id with message: {$e->getMessage()}");
        }

    } // 
        
} // end class
