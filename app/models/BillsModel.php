<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class BillsModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'billable_id' => 'INT',
		'detail' => 'STR',
		'period' => 'STR', 
		'amount' => 'STR',
		'file_id' => 'INT',
		'belongs_to' => 'INT',
		'created_at' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = ['detail', 'period'];

	protected $rules = [

	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}