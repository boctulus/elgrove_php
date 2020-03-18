<?php
namespace simplerest\models;

use simplerest\core\Model;


class PermissionsModel extends Model 
{
	protected $table_name = "permissions";
	protected $id_name = 'id';

	protected $schema = [
		'id' => 'INT',
		'tb' => 'STR',
		'user_id' => 'INT',
		'can_create' => 'INT',
		'can_read' => 'INT',
		'can_update' => 'INT',
		'can_delete' => 'INT', 
		'created_at' => 'STR',
		'updated_at' => 'STR'
	];

	protected $rules = [
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

}







