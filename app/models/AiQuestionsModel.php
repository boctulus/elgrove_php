<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class AiQuestionsModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'question' => 'STR',
		'answer' => 'STR', 
		'keywords' => 'STR',
		'enabled' => 'INT', 
		'created_at' => 'STR',
		'updated_at' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = ['enabled'];

	protected $rules = [
		'enabled' => ['type' =>  'bool']	
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}