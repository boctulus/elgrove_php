<?php

namespace simplerest\tests;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use simplerest\libs\Validator;
use simplerest\core\exceptions\InvalidValidationException;

include 'config/constants.php';

class ValidatorTest extends TestCase
{   
	function testvalidateEmptyRuleException()
    {
        $this->expectException(\InvalidArgumentException::class);
        (new Validator())->validate([],['username'=>'admin','correo'=>'admin@serviya.com']);
    }
	
    function testvalidate()
    {
		// caso base, no hay "reglas" => no se valida nada
		$this->assertTrue(
            (new Validator())->validate(
			[
				'username' => [],
				'correo' => [],
			],
			[
				'username'=>'admin',
				'correo'=>'admin@serviya.com'
			])
		);
				
		// campos requeridos
		$this->assertTrue(
            (new Validator())->validate(
				[
					'username' => ['required'=>true],
					'correo' => ['required'=>false],
				],
				[
					'username'=>'admin',
					'correo'=>'admin@serviya.com'
			])
        );
		
		$this->assertIsArray(
            (new Validator())->validate(
			[
				'username' => ['required'=>true], // vacio
				'correo'   => ['required'=>false],
			],
			[
				'username'=>'', //
				'correo'=>'admin@serviya.com'
			])
        );
		
		$this->assertIsArray((new Validator())->validate(
			[
				'username' => [],
				'correo'   => ['type'=>'string','max'=>'30'],
			],
			[
				'username'=>'admin',
				'correo'=>'administradormundialdelmundoconocidouniversal@serviya.com'
			]));
		
		$this->assertIsArray((new Validator())->validate(
			[
				'nombre' => ['type'=>'notnum']
			],
			
				[
				'nombre'=>'Sebastian2'
				]
			));	
			
		// Si llega vacio, ignoro el chequeo
		$this->assertTrue((new Validator())->validate(
			[
				'fuerza' => ['type'=>'integer','min'=>'30']
			],
			
			[
				'fuerza'=>'' //
			]
			));		

		// valido IPv4 con regex, formato regex:/expresion/	
		// (hay otras formas de validatelas, es solo un ejemplo de uso)
		$this->assertTrue((new Validator())->validate(
			[
				'IPv4' => ['type'=>'regex:/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/']
			],
				[
				'IPv4'=>'192.168.0.27'	
				]
			));	
			
		$this->assertIsArray((new Validator())->validate(
			[
				'IPv4' =>  ['type'=>'regex:/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/']
			],
				[
					'IPv4'=>'192.168.0.300'
				]
			));	
	
    }
	
	function testisTypeExceptionPorTipoEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::isType('Brayan','');
    }
	
	function testisTypeExceptionPorTipoDesconocido()
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::isType('Brayan','entero');
    }
	
	function testisTypeExceptionPorDatoNull()
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::isType(NULL,'int');
    }
	
	function testisTypeExceptionPorRegexInvalida()
    {
        $this->expectException(\InvalidArgumentException::class);
        Validator::isType('190-200-200','regex:/(.*/');
    }
	
	function testisType()
    {
		$this->assertTrue(
			Validator::isType('Brayan','string')
        );
		
		$this->assertFalse(
			Validator::isType('1250','notnum')
        );
		
		$this->assertFalse(
			Validator::isType('Sebastian2','notnum')
        );
		
		$this->assertTrue(
			Validator::isType('1250','string')
        );
		
		$this->assertTrue(
			Validator::isType('','string')
        );
		
		$this->assertTrue(
			Validator::isType('   ','string')
        );
		
		$this->assertTrue(
			Validator::isType('32543','number')
        );
		
		$this->assertTrue(
			Validator::isType('-32543','number')
        );
		
		$this->assertTrue(
			Validator::isType(' 16  ','number')
        );
		
		$this->assertTrue(
			Validator::isType(' 16  ','int')
        );
		
		$this->assertFalse(
			Validator::isType('16.3','int')
        );
		
		$this->assertFalse(
			Validator::isType(' 16.3  ','int')
        );
		
		$this->assertTrue(
			Validator::isType('16.3','decimal')
        );
		
		$this->assertTrue(
			Validator::isType('-.023','decimal')
        );
		
		$this->assertTrue(
			Validator::isType('.023','number')
        );
		
		$this->assertTrue(
			Validator::isType('192.168.0.27','regex:/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/')
        );
		
		$this->assertFalse(
			Validator::isType('192.168.0.300','regex:/^((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?1)){3}\z/')
        );
	}
	
	
}
