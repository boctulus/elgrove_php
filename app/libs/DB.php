<?php

namespace simplerest\libs;

use simplerest\core\Model;

class DB {

	private static $conn;
	private static $model;
	//private static $enabled = false;

    private function __construct() { }

    public static function getConnection() {
		if (self::$conn != null)
			return self::$conn;

		$config = include CONFIG_PATH . 'config.php';

        $db_name = $config['database']['db_name'];
		$host    = $config['database']['host'] ?? 'localhost';
		$user    = $config['database']['user'] ?? 'root';
		$pass    = $config['database']['pass'] ?? '';
		
		try {
			$options = [ \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION ];
			self::$conn = new \PDO("mysql:host=" . $host . ";dbname=" . $db_name, $user, $pass, $options);
            self::$conn->exec("set names utf8");
		} catch (\PDOException $e) {
			throw new \PDOException($e->getMessage());
		}	
		
		return self::$conn;
	}
		
	// Returns last executed query 
	public static function getQueryLog(){
		return static::$model->getLog();
	}
	
	//public static function enableQueryLog(){
	//	static::$enabled = true;
	//}

	public static function table($from, $alias = NULL) {

		// Usar un wrapper y chequear el tipo
		if (stripos($from, ' FROM ') === false){
			$tb_name = $from;
		
			$names = explode('_', $tb_name);
			$names = array_map(function($str){ return ucfirst($str); }, $names);
			$model = implode('', $names).'Model';		

			$class = '\\simplerest\\models\\' . $model;
			$obj = new $class(self::getConnection(), $alias);
			
			if (!is_null($alias))
				$obj->setTableAlias($alias);

			static::$model = $obj;			
			return $obj;	
		}

		$model = new Model(self::getConnection());
		static::$model = $model;

		$st = ($model)->fromRaw($from);	
		return $st;
	}

	public static function beginTransaction(){
		/* 
		  Not much to it! Forcing PDO to throw exceptions instead errors is the key to being able to use the try / catch which simplifies the logic needed to perform the rollback.
		*/
		static::getConnection()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		static::getConnection()->beginTransaction();
	}

	public static function commit(){
		static::getConnection()->commit();
	}

	public static function rollback(){
		static::getConnection()->rollback();
	}

	// https://github.com/laravel/framework/blob/4.1/src/Illuminate/DB/Connection.php#L417
	public static function transaction(\Closure $callback)
    {
		static::beginTransaction();

		try
		{
			$result = $callback();
			static::commit();
		}catch (\Exception $e){
			static::rollBack();
			throw $e;
		}

		return $result;
    }
		
}
