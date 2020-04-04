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
		$auth = new \simplerest\core\api\v1\AuthController();
        return $auth->check();
	}
}
