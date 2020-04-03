<?php

namespace simplerest\models;

use simplerest\core\Model;
use simplerest\libs\Factory;

class FilesModel extends Model
 { 	
	protected $schema = [
		'id' => 'INT',
		'filename' => 'STR',
		'file_ext' => 'STR',
		'filename_as_stored' => 'STR',
		'belongs_to' => 'INT',
		'guest_access' => 'INT',
		'locked' => 'INT',
		'created_at' => 'STR',
		'deleted_at' => 'STR',
	];

	protected $not_fillable = ['filename_as_stored'];
	protected $nullable = ['belongs_to'];

	protected $rules = [
		'filename'	=> ['max'=> 255],
		'locked' 	=> ['type' => 'bool'],
		'guest_access' 	=> ['type' => 'bool']
	];

    function __construct($db = NULL){		
        parent::__construct($db);
    }
	
}