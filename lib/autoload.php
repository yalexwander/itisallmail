<?php

spl_autoload_register(
    function ($class_name): void {
        $includePath = __DIR__ . preg_replace('/\\\\/', DIRECTORY_SEPARATOR, ("\\" . $class_name . ".php"));

        if (file_exists($includePath)) {
            require_once $includePath;
        }
    }
);
