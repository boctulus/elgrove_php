<?php

namespace simplerest\controllers;

use simplerest\core\ResourceController;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\models\RolesModel;

class DumbAuthController extends ResourceController
{
    function __construct()
    {
        parent::__construct();
    }

    function super_cool_action($a)
    {
        var_dump($this->getRoles());
        var_dump($this->isRegistered());

        if (!$this->hasAnyRole(['cajero', 'gerente']))
            Factory::response()->sendError('Unauthorized', 401);

        // acción cualquiera:
        return ++$a;
    }     
    
    function test(){

        $permissions = $this->getPermissions();
        foreach ($permissions as $tb => $perms){
            echo "[$tb]\n";
            $perms = (int) $perms;
            printf("Create: %d, Read: %d, Update: %d, Delete: %d", 
                ($perms & 8) AND 1, 
                ($perms & 4) AND 1, 
                ($perms & 2) AND 1, 
                ($perms & 1) AND 1
            );
        }
    }
}