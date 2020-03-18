<?php

namespace simplerest\libs;

class Strings {

	/*
		WordAnother to word_another
	*/
	static function fromCamelCase($str, $sep = '_'){
		$words = [];
		$wix   = -1;
		$len = strlen($str);
		for ($i=0; $i<$len; $i++){
			if ($str[$i] >= 'A' && $str[$i] <= 'Z'){
				$str[$i] = strtolower($str[$i]);
				$wix++;
			}
			
			if (!isset($words[$wix]))
				$words[$wix] = $str[$i];
			else
				$words[$wix] .= $str[$i];	
		}

		return implode($sep,$words);
	}

	/*
		word_another to WordAnother
	*/
	static function toCamelCase($name){
        return implode('',array_map('ucfirst',explode('_',$name)));
    }

    static function startsWith($needle, $haystack)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    static function endsWith($needle, $haystack)
    {
        return substr($haystack, -strlen($needle))===$needle;
    }

    /**
	 * gen_secret_key - scretet_key generator
	 *
	 * @return string
	 */
	static function gen_secret_key(){
		$arr=[];
		for ($i=0;$i<(512/7);$i++){
			$arr[] = chr(rand(32,38));
			$arr[] = chr(rand(40,47));
			$arr[] = chr(rand(58,64));
			$arr[] = chr(rand(65,90));
			$arr[] = chr(rand(91,96));
			$arr[] = chr(rand(97,122));	
			$arr[] = chr(rand(123,126));
		}	
    
        shuffle($arr);
		return substr(implode('', $arr),0,512);
	}
}


