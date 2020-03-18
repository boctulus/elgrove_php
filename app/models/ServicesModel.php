<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class ServicesModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'text' => 'STR',
		'img' => 'STR',
		'enabled' => 'INT',
		'created_at' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = [];

	protected $rules = [
		'name' 	=> ['min'=>3, 'max'=>80],
		'enabled' => ['type' =>  'bool']
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}