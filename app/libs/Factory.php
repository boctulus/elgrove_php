<?php 

namespace simplerest\libs;

class Factory {
	static function response() {
		return \simplerest\core\Response::getInstance();
	}

	static function request() {
		return \simplerest\core\Request::getInstance();
	}

	static function check(){
		$auth = new \simplerest\controllers\AuthController();
        return $auth->check();
	}
}
