<?php

namespace simplerest\controllers;

use simplerest\core\ResourceController;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\libs\DB;
use simplerest\models\RolesModel;

class DownloadController extends ResourceController
{
    static protected $guest_access = true;
    static protected $owned = true;

    function __construct()
    {
        $this->headers = [];

        parent::__construct();
     
        if ($this->isGuest()){
            if (!static::$guest_access){
                Factory::response()->sendError('Unauthorized!',403, 'There is no guest access');
            }
        }        
    }


    function get($id = null) {
        if ($id == null)
            return;

        $_get = [];    
        
        if (!$this->is_admin && static::$owned)
            $_get[] = ['belongs_to', $this->uid];

        $_get[] =   ['id', $id];   

        $row = DB::table('files')->select(['filename_as_stored'])->where($_get)->first();

        if (empty($row))
            Factory::response()->sendError('File not found', 404);
      
        $file = UPLOADS_PATH . $row['filename_as_stored'];

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }
}