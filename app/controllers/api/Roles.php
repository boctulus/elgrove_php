<?php

namespace simplerest\controllers\api;

use simplerest\controllers\MyApiController; 
use simplerest\libs\Factory;
use simplerest\models\RolesModel;

class Roles extends MyApiController
{
    protected $scope = [
        'guest'      => [], 
        'registered' => ['read'],
        'basic'      => ['read'],
        'regular'    => ['read']
    ];

    function __construct()
    {
        parent::__construct();
    }
        
    function get($id = null){
        $roles_m = new RolesModel();

        if ($id == NULL){
            $rows = [];
            $roles = $roles_m->get_roles();
            foreach ($roles as $idx => $rol){
                $rows[] = [
                    'id' => $idx,
                    'name' => $rol['name'],
                    'is_admin' => $rol['is_admin']
                ];
            }
            return $rows;
        }else{
            $row = $roles_m->get_roles()[$id];
            return [
                'id' => $id,
                'name' => $row['name'],
                'is_admin' => $row['is_admin']
            ];
        }
    }

    function create($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

    function put($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

    function patch($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

    function delete($id = null){
        Factory::response()->sendError('Not implemented', 501, "Roles are read-only");
    }

} // end class
