<?php

use TestApiRest\Debug;

/**
 * Método para realizar o debug de código
 */
if(!function_exists('debug')){
    function debug($var, $exit = true, $save = false)
    {
        return Debug::dump($var, $exit, $save);
    }
}