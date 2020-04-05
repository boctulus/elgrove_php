<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\MultipleUploader;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\libs\Debug;

class Files extends MyApiController
{ 
    //static protected $owned = false;
    static protected $guest_access = true;
    static protected $soft_delete = false;

    function __construct()
    {       
        $this->scope['guest']      = ['read'];
        $this->scope['registered'] = [];
        $this->scope['copropietario'] = ['read'];
        
        parent::__construct();
    }

    function post() {
        $data = $_POST;

        $uploader = (new MultipleUploader())
        ->setFileHandler(function($uid) {
            $prefix = ($uid ?? '0').'-';
            return uniqid($prefix, true);
         }, $this->uid);


        $files    = $uploader->doUpload()->getFileNames();   
        $failures = $uploader->getErrors();     

        if (count($files) == 0){
            Factory::response()->send([
                'uploaded' => [],
                'failures' => $failures
            ], 200);
        }        
        
        $instance = DB::table('files')->fill(['filename_as_stored']);

        $uploaded = [];
        foreach ($files as list($filename_ori, $filename_as_stored)){           
            if ($this->isAdmin()){
                if (isset($data['belongs_to']))
                    $belongs_to = $data['belongs_to'];    
            } else 
                $belongs_to = !$this->isGuest() ? $this->uid : null;    

            $file_ext = pathinfo($filename_ori, PATHINFO_EXTENSION);

            $id = $instance
            ->create([
                        'filename' => $filename_ori,  
                        'file_ext' => $file_ext,
                        'filename_as_stored' => $filename_as_stored,
                        'belongs_to' => $belongs_to ?? NULL,
                        'guest_access' => $data['guest_access'] ?? 0
            ]);

            $uploaded[] = [ 
                            'filename' => $filename_ori,
                            'id' => $id,
                            'link' => '/download/get/' . $id
            ];
        }
  
        Factory::response()->send([
            'uploaded' => $uploaded,
            'failures' => $failures
        ], 201);
        
    }

    function put ($id = null){
        Factory::response()->sendError('Not implemented', 501);
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
            Factory::response()->sendError("Lacks id in request", 400);

        if (!ctype_digit($id))
            Factory::response()->sendError('Bad request', 400, 'Id should be an integer');

        $data = Factory::request()->getBody();        

        try {    
            $instance = DB::table('files');
            $instance->fill(['deleted_at']); 

            $owned = static::get_owned() && $instance->inSchema(['belongs_to']);
            $row   = $instance->where(['id', $id])->first();
            
            if (empty($row)){
                Factory::response()->code(404)->sendError("File with id=$id does not exists");
            }
            
            if ($owned && !$this->is_admin && $row['belongs_to'] != $this->uid){
                Factory::response()->sendError('Forbidden', 403, 'You are not the owner');
            }
            
            $extra = [];

            if ($this->is_admin){
                if ($instance->inSchema(['locked'])){
                    $extra = array_merge($extra, ['locked' => 1]);
                }   
            }else {
                if (isset($row['locked']) && $row['locked'] == 1){
                    Factory::response()->sendError("Locked by Admin", 403);
                }
            }

            if ($instance->inSchema(['deleted_by'])){
                $extra = array_merge($extra, ['deleted_by' => $this->uid]);                
            } 
            
            $soft_delete = static::$soft_delete && $instance->inSchema(['deleted_at']);

            if (!$soft_delete) {
                $path = UPLOADS_PATH . $row['filename_as_stored'];

                if (!file_exists($path)){
                    //var_dump($path);
                    Factory::response()->sendError("File not found",404, $path); 
                }

                $ok = unlink($path);

                if (!$ok){
                    Factory::response()->sendError("File permission error", 500);
                }
            }

            if($instance->delete($soft_delete, $extra)){
                Factory::response()->sendJson("OK");
            }	
            //else
            //    Factory::response()->sendError("File not found",404);

        } catch (\Exception $e) {
            Factory::response()->sendError("Error during DELETE for id=$id with message: {$e->getMessage()}");
        }

    } // 
        
} // end class
