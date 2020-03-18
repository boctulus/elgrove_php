<?php

namespace simplerest\libs;

use simplerest\core\interfaces\IValidator;

/*
	Validador de campos de formulario
	Ver 2.0 Beta

	@author boctulus
	
	Novedad: el desacople de reglas y datos
*/
class Validator implements IValidator
{
	protected $required  = true;
	protected $ignored_fields = [];

	function setRequired(bool $state){
		$this->required = $state;
		return $this;
	}

	function ignoreFields(array $fields){
		$this->ignored_fields = $fields;
		return $this;
	}

	/*
		@param string $dato
		@param string $tipo
		@return mixed
		
		Chequear si es mejor utilizar FILTER_VALIDATE_INT y FILTER_VALIDATE_FLOAT

		https://www.php.net/manual/en/filter.filters.validate.php
		
		
	*/
	static function isType($dato, $tipo){
		if ($dato === NULL)
			throw new \InvalidArgumentException('No data'); 
		
		if (empty($tipo))
			throw new \InvalidArgumentException('Data type is undefined');

		if ($tipo == 'bool' || $tipo == 'boolean'){
			return $dato == 0 || $dato == 1;
		}elseif ($tipo == 'int' || $tipo == 'integer'){
			return preg_match('/^(-?[0-9]+)+$/',trim($dato)) == 1;
		}elseif($tipo == 'decimal' || $tipo == 'float' || $tipo == 'double'){
			$dato = trim($dato);
			return is_numeric($dato);
		}elseif($tipo == 'number'){
			$dato = trim($dato);
			return ctype_digit($dato) || is_numeric($dato);
		}elseif($tipo == 'string' || $tipo == 'str'){
			return is_string($dato);
		}elseif($tipo == 'alpha'){                                     
			return (preg_match('/^[a-z]+$/i',$dato) == 1); 
		}elseif($tipo == 'alpha_num'){                                     
			return (preg_match('/^[a-z0-9]+$/i',$dato) == 1);
		}elseif($tipo == 'alpha_dash'){                                     
			return (preg_match('/^[a-z\-_]+$/i',$dato) == 1); 
		}elseif($tipo == 'alpha_spaces'){                                     
			return (preg_match('/^[a-z ]+$/i',$dato) == 1);  
		}elseif($tipo == 'alpha_utf8'){                                     
			return (preg_match('/^[\pL\pM]+$/u',$dato) == 1); 
		}elseif($tipo == 'alpha_num_utf8'){                                     
			return (preg_match('/^[\pL\pM0-9]+$/u',$dato) == 1);
		}elseif($tipo == 'alpha_dash_utf8'){                                     
			return (preg_match('/^[\pL\pM\-_]+$/u',$dato) == 1); 	
		}elseif($tipo == 'alpha_spaces_utf8'){                                     
			return (preg_match('/^[\pL\pM\p{Zs}]+$/u',$dato) == 1); 		
		}elseif($tipo == 'not_num'){
			return preg_match('/[0-9]+/',$dato) == 0;
		}elseif($tipo == 'email'){
				return filter_var($dato, FILTER_VALIDATE_EMAIL);
		}elseif($tipo == 'url'){
				return filter_var($dato, FILTER_VALIDATE_URL);
		}elseif($tipo == 'mac'){
				return filter_var($dato, FILTER_VALIDATE_MAC);
		}elseif($tipo == 'domain'){
				return filter_var($dato, FILTER_VALIDATE_DOMAIN);
		}elseif($tipo == 'date'){
				return get_class()::isValidDate($dato);
		}elseif($tipo == 'time'){
				return get_class()::isValidDate($dato,'H:i:s');	
		}elseif($tipo == 'datetime'){
				return preg_match('/[1-2][0-9]{3}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-5][0-9]/',$dato)== 1;
		// formato: 'regex:/expresion/'			
		}elseif ((substr($tipo,0,6)=='regex:')){
			try{
				$regex = substr($tipo,6);
				return preg_match($regex,$dato) == 1;	
			}catch(\Exception $e){
				throw new \InvalidArgumentException('Invalid regular expression!');
			}	
		}elseif($tipo == 'array'){
				return is_array($dato);			
		}else
			throw new \InvalidArgumentException(sprintf(_('Invalid data type for %s'), $dato));	
	}	


	/*
		@param array $rules
		@param array $data
		@return mixed

	*/
	function validate(array $rules, array $data){
		//var_export(['rules' => $rules, 'data' => $data]);

		// i18n
        bindtextdomain('validator', LOCALE_PATH);
		textdomain('validator');

		//Debug::dd($data, 'DATA:');

		if (empty($rules))
			throw new \InvalidArgumentException('No validation rules!');
		
		$errores = [];
		
		/*
			Crea array con el campo como índice
		*/
		$push_error = function ($campo, array $error, array &$errores){
			if(isset($errores[$campo]))
				$errores[$campo][] = $error;
			else{
				$errores[$campo] = [];
				$errores[$campo][] = $error;
			}	
		};
			
		$msg = [];
		foreach($rules as $field => $rule){
			//Debug::dd($rule, "RULE $field :");
			
			//var_export(array_diff(array_keys($rule), ['messages']));
			if (isset($rules[$field]['messages'])){
				$msg[$field] = $rule['messages'];
			}				

			//if (isset($data[$field]))			
			//	Debug::dd($data[$field], 'VALOR:');			

			//echo "---------------------------------<p/>\n";
			

			// multiple-values for each field

			if (!isset($data[$field])){
				if ($this->required && isset($rule['required']) && $rule['required']){
					$err = (isset($msg[$field]['required'])) ? $msg[$field]['required'] :  "Field is required";
					$push_error($field,['data'=> null, 'error'=> 'required', 'error_detail' =>_($err)],$errores);
				}	
				
				continue;
			}
				
			foreach ((array) $data[$field] as $dato){
				//Debug::dd(['field' => $field, 'data' => $dato]);

				//var_export($rules[$field]['messages']);
				//var_export(array_diff(array_keys($rule), ['messages']));

				//$constraints = array_diff(array_keys($rule), ['messages']);
				//var_export($constraints);

				if (!isset($dato) || $dato == '' || $dato == null){
					//var_export(['field' =>$dato, 'required' => $rule['required']]);
	
					if ($this->required && isset($rule['required']) && $rule['required']){
						$err = (isset($msg[$field]['required'])) ? $msg[$field]['required'] :  "Field is required";
						$push_error($field,['data'=> null, 'error'=> 'required', 'error_detail' => _($err)],$errores);
					}
	
					continue 2;
				}
				
				if(in_array($field, (array) $this->ignored_fields))
					continue 2;
				
				if (!isset($rule['required']) || $this->required)
					$rule['required'] = false;
	
				$avoid_type_check = false;
				if($rule['required']){
					if(trim($dato)==''){
						$err = (isset($msg[$field]['required'])) ? $msg[$field]['required'] :  "Field is required";
						$push_error($field,['data'=>$dato, 'error'=>'required', 'error_detail' => _($err)],$errores);
					}						
				}	
				
				if (isset($rule['in'])){
					if (!is_array($rule['in']))
						throw new \InvalidArgumentException("in requieres an array");

					$err = (isset($msg[$field]['in'])) ? $msg[$field]['in'] :  sprintf(_("%s is not a valid value"),$dato);
					if (!in_array($dato, $rule['in'])){
						$push_error($field,['data'=>$dato, 'error'=>'in', 'error_detail' => _($err)],$errores);
					}					
				}

				if (isset($rule['not_in'])){
					if (!is_array($rule['not_in']))
						throw new \InvalidArgumentException("in requieres an array");

					$err = (isset($msg[$field]['not_in'])) ? $msg[$field]['not_in'] :  sprintf(_("%s is not a valid value"),$dato);
					if (in_array($dato, $rule['not_in'])){
						$push_error($field,['data'=>$dato, 'error'=>'not_in', 'error_detail' => _($err)],$errores);
					}					
				}

				if (isset($rule['between'])){
					if (!is_array($rule['between']))
						throw new \InvalidArgumentException("between requieres an array");

					if (count($rule['between'])!=2)
						throw new \InvalidArgumentException("between requieres an array of two values");

					if ($dato > $rule['between'][1] || $dato < $rule['between'][0]){
						$err = (isset($msg[$field]['between'])) ? $msg[$field]['between'] : sprintf(_("%s is not between %s and %s"), $dato, $rule['between'][0], $rule['between'][1]);
						$push_error($field,['data'=>$dato, 'error'=>'between', 'error_detail' => _($err)],$errores);
					}					
				}

				if (isset($rule['not_between'])){
					if (!is_array($rule['not_between']))
						throw new \InvalidArgumentException("not_between requieres an array");

					if (count($rule['not_between'])!=2)
						throw new \InvalidArgumentException("not_between requieres an array of two values");

					if (!($dato > $rule['not_between'][1] || $dato < $rule['not_between'][0])){
						$err = (isset($msg[$field]['not_between'])) ? $msg[$field]['not_between'] :  sprintf(_("%s should be less than %s or gretter than %s"), $dato, $rule['not_between'][0], $rule['not_between'][1]);
						$push_error($field,['data'=>$dato, 'error'=>'not_between', 'error_detail' => _($err)],$errores);
					}					
				}

				if (isset($rule['type']) && in_array($rule['type'],['numeric','number','int','integer','float','double','decimal']) && trim($dato)=='')
					$avoid_type_check = true;
				
				if (isset($rule['type']) && !$avoid_type_check)
					if (!get_class()::isType($dato, $rule['type'])){
						$err =  (isset($msg[$field]['type'])) ? $msg[$field]['type'] : "It's not a valid {$rule['type']}";
						$push_error($field,['data'=>$dato, 'error'=>'type', 'error_detail' => _($err)],$errores);
					}						
					
						
				if(isset($rule['type'])){	
					if (in_array($rule['type'],['str','string','not_num','email']) || strpos($rule['type'], 'regex:') === 0 ){
							
							if(isset($rule['min'])){ 
								$rule['min'] = (int) $rule['min'];
								if(strlen($dato)<$rule['min']){
									$err = (isset($msg[$field]['min'])) ? $msg[$field]['min'] :  "The minimum length is %d characters";
									$push_error($field,['data'=>$dato, 'error'=>'min', 'error_detail' => sprintf(_($err),$rule['min'])],$errores);
								}									
							}
							
							if(isset($rule['max'])){ 
								$rule['max'] = (int) $rule['max'];
								if(strlen($dato)>$rule['max']){
									$err = (isset($msg[$field]['max'])) ? $msg[$field]['max'] :  'The maximum length is %d characters';
									$push_error($field,['data'=>$dato, 'error'=>'max', 'error_detail' => sprintf(_($err), $rule['max'])],$errores);
								}
									
							}
					}	
					
					if(in_array($rule['type'],['numeric','number','int','integer','float','double','decimal'])){
							
							if(isset($rule['min'])){ 
								$rule['min'] = (int) $rule['min'];
								if($dato<$rule['min']){
									$err = (isset($msg[$field]['min'])) ? $msg[$field]['min'] :  'Minimum is %d';
									$push_error($field,['data'=>$dato, 'error'=>'min', 'error_detail' => sprintf(_($err), $rule['min'])],$errores);
								}
									
							}
							
							if(isset($rule['max'])){ 
								$rule['max'] = (int) $rule['max'];
								if($dato>$rule['max']){
									$err = (isset($msg[$field]['max'])) ? $msg[$field]['max'] :  'Maximum is %d';

									$push_error($field,['data'=>$dato, 'error'=>'max', 'error_detail' => sprintf(_($err), $rule['max'])],$errores);
								}
									
							}
					}	
					
					if(in_array($rule['type'],['time','date'])){
							
						$t0 = strtotime($dato);
		
						if(isset($rule['min'])){ 
							if($t0<strtotime($rule['min'])){
								$err = (isset($msg[$field]['min'])) ? $msg[$field]['min'] :  'Minimum is '.$rule['min'];
								$push_error($field,['data'=>$dato, 'error'=>'min', 'error_detail' => _($msg)],$errores);
							}
								
						}
						
						if(isset($rule['max'])){ 
							if($t0>strtotime($rule['max'])){
								$err = (isset($msg[$field]['max'])) ? $msg[$field]['max'] : 'Maximum is '.$rule['max'];
								$push_error($field,['data'=>$dato, 'error'=>'max', 'error_detail' => _($msg)],$errores);
							}
								
						}
					}	
					
				}	
			}
							
		}

		$validated = empty($errores) ? true : $errores;

		return $validated;
	}
	
	
	
	private static function isValidDate($date, $format = 'd-m-Y') {
		$dateObj = \DateTime::createFromFormat($format, $date);
		return $dateObj && $dateObj->format($format) == $date;
	}
}


/*
	Helper
*/


// @author: vitalyart dot ru
if (!function_exists('array_key_first')) {
    /**
     * Gets the first key of an array
     *
     * @param array $array
     * @return mixed
     */
    function array_key_first(array $array)
    {
        if (count($array)) {
            reset($array);
            return key($array);
        }

        return null;
    }
}

