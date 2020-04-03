<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class LegalDocumentsModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'file_id' => 'INT',
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