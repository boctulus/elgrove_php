<?php

namespace simplerest\core;

include HELPERS_PATH . 'view.php';

class View
{
    function __construct(string $view_path, array $vars_to_be_passed  = null, $layout = 'app_layout.php')
    {
        if (!empty($vars_to_be_passed))
            extract($vars_to_be_passed);                

        ob_start();
        include VIEWS_PATH . $view_path;
        $content = ob_get_contents();
        ob_end_clean();

        include VIEWS_PATH . "layouts/{$layout}"; 
    }
}