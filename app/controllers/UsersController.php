<?php

namespace simplerest\controllers;

class UsersController extends MyController
{
	function index(){
		$this->view('users.php');
	}
}
	