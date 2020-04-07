<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class SliderModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'img' => 'STR',
		'line1' => 'STR',
		'line2' => 'STR',
		'line3' => 'STR',
		'enabled' => 'INT',
		'created_at' => 'STR',
		'deleted_at' => 'STR'
	];

	protected $nullable = ['enabled'];

	protected $rules = [
		'line1' 	=> ['min'=>1, 'max'=>255],
		'line2' 	=> ['min'=>1, 'max'=>80],
		'line3' 	=> ['min'=>1, 'max'=>255],
		'enabled' => ['type' =>  'bool']
	];

    function __construct($db = NULL){
        parent::__construct($db);
    }	
	
}