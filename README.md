# Simple Autoloader

The Simple autoloader is an alternative of composer!

# Compatibility

Only in PHP 7

[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207-8892BF.svg?style=flat-square)](https://php.net/)

# Usage

After generate the classes.php.cache, you can use this code to use the autoloader:

```php
<?php

$classes = include 'classes.php.cache';

require_once '%DIR%/AutoloaderWithCacheFile.php';

$autoload = new \SimpleAutoloader\AutoloaderWithCacheFile();
$autoload->setClasses($classes)
    ->setDebugLevel(0);

spl_autoload_register([$autoload, 'autoloader'], true, false);
```

Replace %DIR% by the location of AutoloaderWithCacheFile class.
