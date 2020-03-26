<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class MessagesModel extends Model
 { 
	protected $nullable = ['phone', 'attempts', 'sent_at'];

	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'email' => 'STR',
		'phone' => 'STR',
		'subject' => 'STR',
		'content'=> 'STR',
		'created_at' => 'STR',
		'deleted_at' => 'STR',
		'sent_at' => 'INT'
	];

	protected $rules = [
		'email' 	=> ['type' => 'email'],
		'name' 	=> ['min'=>3, 'max'=>80],
		'content' => ['min'=> 5]	
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}