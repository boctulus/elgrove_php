<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class DocumentsModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'file' => 'STR',
		'enabled' => 'INT',
		'belongs_to' => 'INT',
		'created_at' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = ['enabled'];

	protected $rules = [
		'name' 	=> ['min'=>3, 'max'=>80],
		'enabled' => ['type' =>  'bool']	
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}