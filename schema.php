<?php

/*
	preg_match destructivo
*/
function slice(string &$str, string $pattern, callable $fn = NULL) {
	if (preg_match($pattern,$str,$matches)){
		$pos = strpos($str, $matches[1]) + strlen($matches[1]);
		$str = substr($str, $pos);
		
		if ($fn != NULL)
			$matches[1] = call_user_func($fn, $matches[1]);
		
		return $matches[1];
	}
}

/*
	preg_match destructivo
*/
function slice_all(string &$str, string $pattern) {
	if (preg_match($pattern,$str,$matches)){
		$pos = strpos($str, $matches[1]) + strlen($matches[1]);
		$str = substr($str, $pos);
		
		return array_slice($matches, 1);
	}
}

class Schema {
	public $tb_name;
	public $engine;
	public $charset = 'utf8';
	public $collation;
	
	protected $fields  = [];
	protected $current_field;
	protected $fks = [];
	protected $indices = [];
	
	function __construct($name){
		$this->tb_name = $name;
	}	
	
	// type
	
	function int(string $name, int $len = NULL){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'INT';
		
		if ($len != NULL)
			$this->fields[$this->current_field]['len'] = $len;
		
		return $this;		
	}	
	
	function integer(string $name, int $len = NULL){
		$this->int($name, $len);
		return $this;		
	}	
	
	function serial(string $name, int $len = NULL){		
		$this->current_field = $name;
		//$this->bigint($name, $len)->unsigned()->auto()->unique();
		$this->fields[$this->current_field]['type'] = 'SERIAL';
		return $this;		
	}	
	
	function bigint(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'BIGINT';
		return $this;		
	}	
	
	function mediumint(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'MEDIUMINT';
		return $this;		
	}	
	
	function smallint(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'SMALLINT';
		return $this;		
	}	
	
	function tinyint(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'TINYINT';
		return $this;		
	}	
	
	function boolean(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'BOOLEAN';
		return $this;		
	}	
	
	function bool(string $name){
		$this->boolean($name);
		return $this;		
	}
	
	function bit(string $name, int $len){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'BIT';
		$this->fields[$this->current_field]['len'] = $len;		
		return $this;		
	}
	
	function decimal(string $name, int $len = 15, int $len_dec = 4){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DECIMAL';
		$this->fields[$this->current_field]['len'] = [$len, $len_dec];		
		return $this;		
	}	
	
	function float(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'FLOAT';
		return $this;		
	}	
	
	function double(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DOUBLE';
		return $this;		
	}	
	
	function real(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'REAL';
		return $this;		
	}	
	
	function char(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'CHAR';
		return $this;		
	}	
	
	function varchar(string $name, int $len = 60){
		if ($len > 65535)
			throw new \InvalidArgumentException("Max length is 65535");
		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'VARCHAR';
		$this->fields[$this->current_field]['len'] = $len;
		return $this;		
	}	
	
	function text(string $name, int $len = NULL){
		if ($len > 65535)
			throw new \InvalidArgumentException("Max length is 65535");
		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'TEXT';
		
		if ($len != NULL)
			$this->fields[$this->current_field]['len'] = $len;
		
		return $this;		
	}	
	
	function tinytext(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'TINYTEXT';
		return $this;		
	}
	
	function mediumtext(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'MEDIUMTEXT';
		return $this;		
	}
	
	function longtext(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'LONGTEXT';
		return $this;		
	}
	
	function varbinary(string $name, int $len = 60){
		if ($len > 65535)
			throw new \InvalidArgumentException("Max length is 65535");
		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'VARBINARY';
		$this->fields[$this->current_field]['len'] = $len;
		return $this;		
	}
	
	function blob(string $name){
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'BLOB';
		return $this;		
	}	
	
	function binary(string $name, int $len){
		if ($len > 255)
			throw new \InvalidArgumentException("Max length is 65535");
		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'BINARY';
		$this->fields[$this->current_field]['len'] = $len;
		return $this;		
	}
	
	function tinyblob(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'TINYBLOB';
		return $this;		
	}
	
	function mediumblob(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'MEDIUMBLOB';
		return $this;		
	}
	
	function longblob(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'LONGBLOB';
		return $this;		
	}
	
	function json(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'JSON';
		return $this;		
	}
	
	function set(string $name, array $values){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'SET';
		$this->fields[$this->current_field]['array'] = $values;
		return $this;		
	}
	
	function enum(string $name, array $values){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'ENUM';
		$this->fields[$this->current_field]['array'] = $values;
		return $this;		
	}
	
	function time(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'TIME';
		return $this;		
	}
	
	function year(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'YEAR';
		return $this;		
	}
	
	function date(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DATE';
		return $this;		
	}
	
	function datetime(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DATETIME';
		return $this;		
	}
	
	function timestamp(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'TIMESTAMP';
		return $this;		
	}
	
	function softDeletes(){		
		$this->current_field = 'deleted_at';
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DATETIME';
		return $this;		
	}
	
	function datetimes(){		
		$this->current_field = 'created_at';
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DATETIME';
		$this->current_field = 'updated_at';
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'DATETIME';
		return $this;		
	}
	
	function point(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'POINT';
		return $this;		
	}
	
	function multipoint(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'MULTIPOINT';
		return $this;		
	}
	
	function linestring(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'LINESTRING';
		return $this;		
	}
	
	function polygon(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'POLYGON';
		return $this;		
	}
	
	function multipolygon(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'MULTIPOLYGON';
		return $this;		
	}
	
	function geometry(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'GEOMETRY';
		return $this;		
	}
	
	function geometrycollection(string $name){		
		$this->current_field = $name;
		$this->fields[$this->current_field] = [];
		$this->fields[$this->current_field]['type'] = 'GEOMETRYCOLLECTION';
		return $this;		
	}	
	
	// collation && charset 
	
	function collation(string $val){
		$this->fields[$this->current_field]['collation'] = $val;
		return $this;		
	}
	
	function charset(string $val){
		$this->fields[$this->current_field]['charset'] = $val;
		return $this;		
	}
	
	// modifiers
	
	function auto(){
		$this->fields[$this->current_field]['auto'] =  true;
		return $this;
	}

	function nullable(bool $value =  true){
		$this->fields[$this->current_field]['nullable'] =  $value ? 'NULL' : 'NOT NULL';
		return $this;
	}
	
	function comment($string){
		$this->fields[$this->current_field]['comment'] =  $string;
		return $this;
	}
	
	function default($val = NULL){
		if ($val == NULL)
			$val = 'NULL';
		
		$this->fields[$this->current_field]['default'] =  $val;
		return $this;
	}
	
	function currentTimestamp(){
		$this->default('current_timestamp()');	
		return $this;
	}
	
	protected function setAttr($attr){
		$this->fields[$this->current_field]['attr'] = $attr;
	}
	
	function unsigned(){
		$this->setAttr('UNSIGNED');
		return $this;
	}
	
	function zeroFill(){
		$this->setAttr('ZEROFILL');
		return $this;
	}
	
	function binaryAttr(){
		$this->setAttr('BINARY');
		return $this;
	}
	
	// ALTER TABLE `aaa` ADD `ahora` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `fecha`;
	function onUpdateCurrent(){
		$this->setAttr('current_timestamp()');	
		return $this;
	}
	
	function after(string $field){
		$this->fields[$this->current_field]['after'] =  $field;
		return $this;
	}
	
	// ALTER TABLE `aaa` ADD `inicio` INT NOT NULL FIRST;
	function first(){
		if (isset($this->fields[$this->current_field]['after']))
			unset($this->fields[$this->current_field]['after']);
		
		foreach ($this->fields as $k => $field){
			if (isset($this->fields[$k]['first']))
				unset($this->fields[$k]['first']);
		}	
		
		$this->fields[$this->current_field]['first'] =  true;
		return $this;
	}
	
	// FKs
	
	function foreign(string $name){
		$this->current_field = $name;
		$this->fks[$this->current_field] = [];
		return $this;
	}
	
	function references(string $field){
		$this->fks[$this->current_field]['references'] = $field;
		return $this;
	}
	
	function on(string $table){
		$this->fks[$this->current_field]['on'] = $table;
		return $this;
	}
	
	function onDelete(string $action){
		$this->fks[$this->current_field]['on_delete'] = $action;
		return $this;
	}
	
	function onUpdate(string $action){
		$this->fks[$this->current_field]['on_update'] = $action;
		return $this;
	}
	
	// INDICES >>>
	
	protected function setIndex(string $type){
		if (!in_array($type, ['PRIMARY', 'UNIQUE', 'INDEX', 'FULLTEXT', 'SPATIAL']))
			throw new \InvalidArgumentException("Invalid index $type");
		
		$this->indices[$this->current_field] = $type;
	}
	
	function primary(){
		$this->setIndex('PRIMARY');
		return $this;
	}
	
	function pri(){
		$this->primary();
		return $this;
	}
	
	function unique(){
		$this->setIndex('UNIQUE');
		return $this;
	}
	
	function index(){
		$this->setIndex('INDEX');
		return $this;
	}
	
	function fulltext(){
		$this->setIndex('FULLTEXT');
		return $this;
	}
	
	function spatial(){
		$this->setIndex('SPATIAL');
		return $this;
	}
	
	///////////////////////////////
	
	/*
		`nombre_campo` tipo[(longitud)] [(array_set_enum)] [charset] [collate] [attributos] NULL|NOT_NULL [default] [AUTOINCREMENT]
	*/
	function getDefinition($field){
		$cmd = '';		
		if (in_array($field['type'], ['SET', 'ENUM'])){
			$values = implode(',', array_map(function($e){ return "'$e'"; }, $field['array']));	
			$cmd .= "($values) ";
		}else{
			if (isset($field['len'])){
				$len = implode(',', (array) $field['len']);	
				$cmd .= "($len) ";
			}else
				$cmd .= " ";	
		}
		
		if (isset($field['attr'])){
			$cmd .= "{$field['attr']} ";
		}
		
		if (isset($field['charset'])){
			$cmd .= "CHARACTER SET {$field['charset']} ";
		}
		
		if (isset($field['collation'])){
			$cmd .= "COLLATE {$field['collation']} ";
		}
			
		if (isset($field['nullable'])){
			$cmd .= "{$field['nullable']} ";
		}else
			$cmd .= "NOT NULL ";

		if (isset($field['default'])){
			$cmd .= "DEFAULT {$field['default']} ";
		}

		if (isset($field['auto'])){
			$cmd .= "AUTO_INCREMENT ";
		}
		
		return trim($cmd);
	}
	
	function create(){
		if (empty($this->fields))
			throw new \Exception("No fields!");
		
		$commands = [
			'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
			'SET AUTOCOMMIT = 0;',
			'START TRANSACTION;',
			'SET time_zone = "+00:00";'
		];
	
		$cmd = '';
		foreach ($this->fields as $name => $field){
			$cmd .= "`$name` {$field['type']} ";			
			$cmd .= $this->getDefinition($field);			
			$cmd .= ",\n";
		}
		
		$cmd = substr($cmd,0,strlen($cmd)-2);
		
		$cmd = "CREATE TABLE `{$this->tb_name}` (\n$cmd\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset};";
		
		$commands[] = $cmd;
		
		// Indices
		
		if (count($this->indices) >0)
		{			
			$cmd = '';		
			foreach ($this->indices as $nombre => $tipo){
				$cmd .= 'ADD ';
				
				switch ($tipo){
					case 'INDEX':
						$cmd .= "INDEX (`$nombre`),\n";
					break;
					case 'PRIMARY':
						$cmd .= "PRIMARY KEY (`$nombre`),\n";
					break;
					case 'UNIQUE':
						$cmd .= "UNIQUE KEY `$nombre` (`$nombre`),\n";
					break;
					case 'SPATIAL':
						$cmd .= "SPATIAL KEY `$nombre` (`$nombre`),\n";
					break;
					
					default:
						throw new \Exception("Invalid index type");
				}				
			}
			
			$cmd = substr($cmd,0,-2);
			$cmd = "ALTER TABLE `{$this->tb_name}` \n$cmd;";
			
			$commands[] = $cmd;
		}		
		
		
		// FKs
		
		// FOREIGN KEY (`abono_id`) REFERENCES `abonos` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
		foreach ($this->fks as $name => $fk){
			$on_delete = !empty($fk['on_delete']) ? 'ON DELETE '.$fk['on_delete'] : '';
			$on_update = !empty($fk['on_update']) ? 'ON UPDATE '.$fk['on_update'] : '';
			
			var_dump("FOREIGN KEY `($name)` REFERENCES `{$fk['on']}` (`{$fk['references']}`) {$fk['on']} $on_delete $on_update");
		}
		exit; //		
				
		$commands[] = 'COMMIT;';		
				
		return implode(' ',$commands)."\n";
	}
	
	function dropTable(){
		return "DROP TABLE `{$this->tb_name}`;\n";
	}
	
	private function showTable(){
		$username = 'root';
		$password = '';
		$hostname = 'localhost';
		$database = 'superpos';
		
		$table    = $this->tb_name;

		try {
			$pdo = new PDO("mysql:host={$hostname};dbname={$database}", $username, $password);
		}
		catch(PDOException $e) {
			die("Could not connect to the database");
		}
		
		$stmt = $pdo->query("SHOW CREATE TABLE `$table`", PDO::FETCH_ASSOC);
		$res  = $stmt->fetch();
		
		return $res;
	}
	
	// From DB
	private function fromDB(){
		$lines = explode("\n", $this->showTable()["Create Table"]);
		$lines = array_map(function($l){ return trim($l); }, $lines);
		
		$fields = [];
		$cnt = count($lines)-1;
		for ($i=1; $i<$cnt; $i++){
			$str = $lines[$i];
			
			if ($lines[$i][0] == '`'){

				$field 		= NULL;
				$type  		= NULL;
				$array		= NULL;				
				$len   		= NULL;
				$charset  	= NULL;
				$collation 	= NULL;
				$nullable	= NULL;
				$default	= NULL;
				$auto 		= NULL;
				$check 		= NULL;
				
				$field      = slice($str, '/`([a-z_]+)`/i');
				$type       = slice($str, '/([a-z_]+)/i');

				if ($type == 'enum' || $type == 'set'){
					$array = slice($str, '/\((.*)\)/i');
				}else{
					$len = slice($str, '/\(([0-9,]+)\)/');					
				}
				
				$charset    = slice($str, '/CHARACTER SET ([a-z0-9_]+)/');
				$collation  = slice($str, '/COLLATE ([a-z0-9_]+)/');
				$nullable   = slice($str, '/(NULL|NOT NULL)/');
				$default    = slice($str, '/DEFAULT ([a-zA-Z0-9_\(\)]+)/');
				$auto       = slice($str, '/(AUTO_INCREMENT)/');
				
				// [CONSTRAINT [symbol]] CHECK (expr) [[NOT] ENFORCED]	
				$check      = slice($str, '/CHECK (\(.*)/', function($s){
					$s = substr($s, 1);
					
					if ($s[strlen($s)-1] == '"')
						$s = substr($s, 0, -1);
					
					if ($s[strlen($s)-1] == ',')
						$s = substr($s, 0, -1);
					
					$s = substr($s, 0, -1);
					
					return $s;
				});
				
				//if (strlen($str)>1)
				//	throw new \Exception("Parsing error!");				
				
				var_dump($lines[$i]);
				var_dump($field);
				var_dump($type);
				var_dump($array);
				var_dump($len);
				var_dump($charset);
				var_dump($collation);
				var_dump($nullable);
				var_dump($default);
				var_dump($auto);
				var_dump($check);
				echo "-----------\n";
			}else{
				// son índices de algún tipo
				var_dump($str);
				
				$primary = slice($str, '/PRIMARY KEY \(`([a-zA-Z0-9_]+)`\)/');				
				$unique  = slice_all($str, '/UNIQUE KEY `([a-zA-Z0-9_]+)` \(`([a-zA-Z0-9_]+)`\)/');
				$index   = slice_all($str, '/KEY `([a-zA-Z0-9_]+)` \(`([a-zA-Z0-9_]+)`\)/');
				
				var_dump($primary);
				var_dump($unique);
				var_dump($index);
				echo "-----------\n";				
			}
		}
		
		/*
			[fields]
			[indices]
		*/
		
		//return $arr;
	}
	
	// ALTER TABLE `users` CHANGE `lastname` `lastname` VARCHAR(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
	
	// ALTER TABLE `users` CHANGE `id` `id` INT(20) UNSIGNED NOT NULL;
	function change(){
		
		$this->fromDB();  /////
		exit;
		
		$name  = $this->current_field;		
		$field = $this->fields[$this->current_field];
		
		$charset   = "CHARACTER SET {$this->charset}";
		$collation = "COLLATE {$this->collation}";
		
		$def = "{$this->fields[$this->current_field]['type']}";		
		if (in_array($field['type'], ['SET', 'ENUM'])){
			$values = implode(',', array_map(function($e){ return "'$e'"; }, $field['array']));	
			$def .= "($values) ";
		}else{
			if (isset($field['len'])){
				$len = implode(',', (array) $field['len']);	
				$def .= "($len) ";
			}else
				$def .= " ";	
		}
		
		if (isset($field['attr'])){
			$def .= "{$field['attr']} ";
		}
		
		if (in_array($field['type'], ['CHAR', 'VARCHAR', 'TEXT', 'TINYTEXT', 'MEDIUMTEXT', 'LONGTEXT', 'JSON', 'SET', 'ENUM'])){
			$def .= "$charset $collation ";	
		}		
		
		if (isset($field['nullable'])){
			$def .= "{$field['nullable']} ";
		}else
			$def .= "NOT NULL ";
			
		if (isset($field['default'])){
			$def .= "DEFAULT {$field['default']} ";
		}
		
		$def = trim($def);
		
		return "ALTER TABLE `{$this->tb_name}` CHANGE `$name` `$name` $def;\n";
	}	
	
	// https://popsql.com/learn-sql/mysql/how-to-rename-a-column-in-mysql/
	function renameColumn($ori, $final){
		return "ALTER TABLE `{$this->tb_name}` RENAME COLUMN `$ori` TO `$final`;\n";
	}
	
	// https://stackoverflow.com/questions/1463363/how-do-i-rename-an-index-in-mysql
	function renameIndex($ori, $final){
		return "ALTER TABLE `{$this->tb_name}` RENAME INDEX `$ori` TO `$final`;\n";
	}
		
	function dropColumn($name){
		return "ALTER TABLE `{$this->tb_name}` DROP `$name`;\n";
	}
	
	function dropIndex($name){
		return "ALTER TABLE `{$this->tb_name}` DROP INDEX `$name`;\n";
	}
	
	function dropPrimary($name){
		return "ALTER TABLE `{$this->tb_name}` DROP PRIMARY INDEX `$name`;\n";
	}

	
	// reflexion
	
	function getSchema(){
		return $this->fields;
	}
}


function debug($s){
	echo '<pre>';
	var_dump($s);
	echo '</pre>';
}

/*

https://www.w3resource.com/mysql/mysql-data-types.php
https://manuales.guebs.com/mysql-5.0/spatial-extensions.html

*/

$table = new Schema('facturas');

$table->engine    = 'InnoDB';
$table->charset   = 'utf8';
$table->collation = 'utf8_general_ci';

$table->int('edad')->unsigned();
$table->varchar('firstname');
$table->varchar('lastname')->nullable()->charset('utf8')->collation('utf8_unicode_ci');
$table->varchar('password', 128);
$table->char('password_char');
$table->varbinary('texto_vb', 300);

// BLOB and TEXT columns cannot have DEFAULT values.
$table->text('texto');
$table->tinytext('texto_tiny');
$table->mediumtext('texto_md');
$table->longtext('texto_long');
$table->blob('codigo');
$table->tinyblob('blob_tiny');
$table->mediumblob('blob_md');
$table->longblob('blob_long');
$table->binary('bb', 255);
$table->json('json_str');

$table->serial('id')->pri();
$table->int('karma')->default(100);
$table->int('code')->zeroFill();
$table->bigint('big_num');
$table->bigint('ubig')->unsigned();
$table->mediumint('medium');
$table->smallint('small');
$table->tinyint('tiny');
$table->decimal('saldo');
$table->float('flotante');
$table->double('doble_p');
$table->real('num_real');

$table->bit('some_bits', 3)->index();
$table->boolean('active')->default(1);
$table->boolean('paused')->default(true);

$table->set('flavors', ['strawberry', 'vanilla']);
$table->enum('role', ['admin', 'normal']);


/*
	The major difference between DATETIME and TIMESTAMP is that TIMESTAMP values are converted from the current time zone to UTC while storing, and converted back from UTC to the current time zone when retrieved. The datetime data type value is unchanged.
*/

$table->time('hora');
$table->year('birth_year');
$table->date('fecha')->first();
$table->datetime('vencimiento')->nullable()->after('num_real');
$table->timestamp('ts')->currentTimestamp()->comment('some comment')->first(); // solo un first


$table->softDeletes(); // agrega DATETIME deleted_at 
$table->datetimes();  // agrega DATETIME(s) no-nullables created_at y deleted_at

$table->integer('id')->auto()->unsigned()->pri();
$table->varchar('correo')->unique();

$table->foreign('factura_id')->references('id')->on('facturas')->onDelete('no action');
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('restrict');

////var_dump($table->getSchema());
/////exit;


echo $table->create();
/*
echo $table->renameColumn('codigo', 'binario');
echo $table->renameIndex('id', 'user_id');
echo $table->dropColumn('geo');
echo $table->dropIndex('bit');
echo $table->dropPrimary('bit');
echo $table->dropTable();
*/
//echo $table->varchar('password', 30)->change();


