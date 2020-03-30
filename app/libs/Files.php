<?php 

namespace simplerest\libs;

class Files {
    // @author Federkun
    static function mkdir_ignore($dir){
		if (!is_dir($dir)) {
			if (false === @mkdir($dir, 0777, true)) {
				throw new \RuntimeException(sprintf('Unable to create the %s directory', $dir));
			}
		}
	}

}    