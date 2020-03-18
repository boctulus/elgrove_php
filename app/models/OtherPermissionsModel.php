<?php
namespace simplerest\models;

use simplerest\core\Model;

class OtherPermissionsModel extends Model 
{
	protected $table_name = 'other_permissions';
	protected $id_name = 'id';
	//protected $fillable = ['folder_id', 'belongs_to', 'guest', 'r', 'w'];
 
	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		'id' => 'INT',
		'folder_id' => 'INT',
		'belongs_to' => 'INT',
		'guest' => 'INT',
		'r' => 'INT',
		'w' => 'INT'			
	];

    function __construct($db = NULL){
		parent::__construct($db);
	}

}







