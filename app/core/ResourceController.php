<?php

namespace simplerest\core;

use simplerest\core\ResourceController;
use simplerest\core\Request;
use simplerest\libs\Factory;
use simplerest\models\RolesModel;
use simplerest\controllers\AuthController;

abstract class ResourceController extends Controller
{
    protected $auth_payload;
    protected $roles;
    protected $uid;
    protected $is_admin;

    function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            Factory::response()->sendCode(200);
        }

        if (Factory::request()->header('Authorization') == NULL){
            $this->uid = null;
            $this->is_admin = false;
            $this->roles = ['guest'];
        }

        $this->auth_payload = (new AuthController())->check();
            
        if (!empty($this->auth_payload)){
            $this->uid = $this->auth_payload->uid; 
            $this->permissions = $this->auth_payload->permissions ?? NULL;

            $r = new RolesModel();
            $this->roles  = $this->auth_payload->roles;              
            
            //var_dump($this->roles); ///

            $this->is_admin = false;
            foreach ($this->roles as $role){
                if ($role != 'registered' && $role != 'guest' && $r->is_admin($role)){
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
                    
        parent::__construct();
    }

    protected function is_admin(){
        return $this->is_admin;
    }

    protected function getRoles(){
        return $this->roles;
    }

    protected function getPermissions(string $table = NULL){
        if ($table == NULL)
            return $this->permissions->$table;

        if (!isset($this->permissions->$table))
            return NULL;

        return $this->permissions->$table;
    }

    protected function isGuest(){
        return $this->roles == ['guest'];
    }

    protected function isRegistered(){
        return !$this->isGuest();
    }

    protected function isAdmin(){
        return $this->is_admin;
    }

    protected function hasRole(string $role){
        return in_array($role, $this->roles);
    }

    protected function hasAnyRole(array $authorized_roles){
        $authorized = false;
        foreach ((array) $this->roles as $role)
            if (in_array($role, $authorized_roles))
                $authorized = true;

        return $authorized;        
    }
    
    
}  