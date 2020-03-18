<?php

namespace simplerest\core\interfaces;

use simplerest\core\Controller;

interface ITransformer {
    function transform(object $user, Controller $controller = NULL);
}