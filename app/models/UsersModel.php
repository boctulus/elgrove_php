<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class UsersModel extends Model
 { 
	protected $table_name = "users";
	protected $id_name = 'id';
	protected $not_fillable = ['confirmed_email', 'enabled'];
	protected $nullable = ['name', 'enabled', 'email', 'confirmed_email'];
	protected $hidden   = [	'password' ];

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'username' => 'STR',
		'enabled' => 'INT',
		'locked' => 'INT',
		'password' => 'STR',
		'created_at' => 'STR',
		'deleted_at' => 'STR',
		'belongs_to' => 'INT'
	];

	protected $rules = [
		'enabled' 	=> ['type' => 'bool'],
		'username'	=> ['min' => 2, 'max'=> 15, 'type' => 'regex:/^[a-zA-Z0-9_]+$/', 'messages' => ['type' => 'Invalid characters'] ], 
	];

    function __construct($db = NULL){		
		$this->registerInputMutator('password', function($pass){ return password_hash($pass, PASSWORD_DEFAULT); });
        parent::__construct($db);
    }
	
}