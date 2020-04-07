<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class PostsModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'title' => 'STR',
		'content' => 'STR',
		'slug' => 'STR',
		'enabled' => 'STR',
		'created_at' => 'STR',
		'updated_at' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = [];

	protected $rules = [
		'title' 	=> ['min'=>3, 'max'=>80],
		'slug' 	=> ['min'=>3, 'max'=>80]
		//'enabled' => ['type' =>  'bool']
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}