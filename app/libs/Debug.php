<?php

namespace simplerest\libs;

class Debug
{
	static function dd($v, $msg=null, $exit=false, $prettify = true) 
	{			
		if (gettype($v)=='boolean'){
			echo ($v ? "TRUE" : "FALSE");
		}	
	
		if (php_sapi_name() == 'cli')
		{
			if ($msg!="")
				echo $msg."\n";

			print_r($v);	
		}else{	
			if ($msg!="")
				echo $msg."<br/>";
			
			if ($prettify){
				print '<pre>';
				print_r($v);
				print '</pre>';
			}else
				print_r($v);	
		}
		
		if ($exit)		
			exit;				
	}		

	// devuelve un var_dump() como json 
	static function json_var_dump($ar){		
		return json_encode(var_export($ar));			
	}	

	static function json_var_dump_v2($var){
		ob_start();
		var_dump($var);
		return json_encode(ob_get_clean());            
	}     


	/** 
	*	@author https://stackoverflow.com/users/1709587
	*/
	static function isAssoc(array $arr)
	{
		if (array() === $arr) return false;
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from 
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 * 
	 * @author maerlyn https://stackoverflow.com/users/308825/maerlyn
	 */
	static function interpolateQuery($query, $params) {
		$keys = array();

		# build a regular expression for each parameter

		if (!isAssoc($params)){
			$_params = [];  // associative array
			foreach($params as $param){
				$key = $param[0];
				$value = $param[1];
				// $type = $param[2];
				$_params[$key] = $value;
			}
			$params  = $_params;
		}		

		foreach ($params as $key => $value) {
			if (is_string($key)) {
				$keys[] = '/'.((substr($key,0,1)==':') ? '' : ':').$key.'/';
			} else {
				$keys[] = '/[?]/';
			}
		}
		
		$query = preg_replace($keys, $params, $query, 1, $count);

		#trigger_error('replaced '.$count.' keys');

		return $query;
	}
}