<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class ContactDataModel extends Model
 { 
	protected $table_name = "contact_data";
	//protected $id_name = 'id';
	protected $nullable = ['phone', 'email', 'address'];

	/*
		Types are INT, STR and BOOL among others
		see: https://secure.php.net/manual/en/pdo.constants.php 
	*/
	protected $schema = [
		'id' => 'INT',
		'phone' => 'STR',
		'email' => 'STR',
		'address' => 'INT',
	];

	protected $rules = [
		
	];

    function __construct($db = NULL){		
        parent::__construct($db);
    }
	
}