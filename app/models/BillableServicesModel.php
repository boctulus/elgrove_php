<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class BillableServicesModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'name' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = [];

	protected $rules = [

	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}