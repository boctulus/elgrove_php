<?php

namespace simplerest\core\interfaces;

interface IValidator {
    function validate(array $rules, array $data);
}