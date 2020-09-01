<?php
function myAutoload($class){
    $parts  = explode('\\', $class);
    $class_name = end($parts);

    $model = __DIR__ . '/Models/' . $class_name . '.php';
    $controller = __DIR__ . '/Controllers/'. $class_name . '.php';
    if (file_exists($model)) {
        require_once $model;
    } elseif (file_exists($controller)) {
        require_once $controller;
    } else {
        echo '<pre>';
        debug_print_backtrace();
        echo '</pre>';
        die();
    }

}

spl_autoload_register('myAutoload');