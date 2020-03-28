<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class CollectionsModel extends Model
 { 
	protected $table_name = "collections";
	protected $id_name = 'id';
	
	protected $schema = [
		'id' => 'INT',
		'entity' => 'STR',
		'refs' => 'STR',		
		'created_at' => 'STR',
		'belongs_to' => 'INT'
	];

	protected $rules = [
	
	];

    function __construct($db = NULL){		
        parent::__construct($db);
    }
	
}