<?php

namespace simplerest\controllers;

use simplerest\libs\Debug;
use simplerest\libs\DB;
use simplerest\libs\Factory;

class HomeController extends MyController
{
	function index(){
		
		try {
			$estado_db = DB::table('users')->get() !== false ? 'OK' : 'en fallo';
		} catch (\Exception $e) {
			$estado_db = $e->getMessage();
		} finally {
			return "<div style='text-align:center;'>
			<h1>El Grove (backend)</h1>
				Base de datos: $estado_db
			</div>";
		}			
	}
}
	