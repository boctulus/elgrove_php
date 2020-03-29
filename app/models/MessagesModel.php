<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class MessagesModel extends Model
 { 
	protected $nullable = ['phone', 'attempts', 'sent_at', 'ip'];
	protected $not_fillable = ['sent_at'];

	protected $schema = [
		'id'    => 'INT',
		'name'  => 'STR',
		'email' => 'STR',
		'phone' => 'STR',
		'ip'      => 'INT',
		'subject' => 'STR',
		'content' => 'STR',	
		'created_at' => 'STR',
		'deleted_at' => 'STR',
		'sent_at'    => 'INT'
	];

	protected $rules = [
		'email' 	=> ['type' => 'email'],
		'name'    	=> ['min'=>3, 'max'=>80],
		'ip'    	=> ['type' => 'ip'],
		'content'   => ['min'=> 5]	
	];

    function __construct($db = NULL){
		$this->registerInputMutator('ip', function(){ 
			$ip = $_SERVER['REMOTE_ADDR']; 
			return ($ip == '127.0.0.1' ? NULL : ip2long($ip)); 
		});  

		parent::__construct($db);
	}
	
}