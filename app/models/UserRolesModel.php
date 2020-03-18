<?php
declare(strict_types=1);

namespace simplerest\models;

use simplerest\core\Model;

class UserRolesModel extends Model
 {
	protected $table_name = "user_roles";
	protected $id_name = 'id';
	protected $hidden = [ ];

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		'id' => 'INT',
		'belongs_to' => 'INT',
		'role_id' => 'INT',
		'created_at'  => 'STR',
		'modification_date'  => 'STR'
	];

	protected $rules = [
		'role_id'	=> ['in' => [2,3,100], 'messages' => ['in' => 'Invalid role_id'] ] 
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }
	
	
}