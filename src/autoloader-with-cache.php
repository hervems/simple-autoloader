<?php

return function ($class) use ($classes, $verbose)
{
    if (isset($classes[$class])) {
        $file = $classes[$class];
        if (file_exists($file)) {
            require_once $file;
        } else {
            if ($verbose) {
                throw new \Exception('File "' . $file . '" not found !');
            }
        }
    } else {
        if ($verbose) {
            throw new \Exception('Class "' . $class . '" not found !');
        }
    }
};
