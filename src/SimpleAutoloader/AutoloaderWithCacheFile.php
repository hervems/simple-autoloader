<?php

/**
 * Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @licence   LGPL
 */

namespace SimpleAutoloader;

/**
 * Simple autoloader with cache file.
 */
class AutoloaderWithCacheFile
{
    /**
     * Classes cache array:
     *  - key => The class name with namespace
     *  - value => The php file with directoy contains the class
     *
     * @var array
     */
    private $classes = [];
    
    /**
     * Level of debug
     *
     * @var int
     */
    private $debugLevel = 0;

    /**
     * Set classes cache array.
     *
     * @param  array $classes
     * @return SimpleAutoloaderWithCacheFile
     */
    public function setClasses(array $classes)
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * Set level of debug.
     *
     * @param  int $level Level of debug
     * @return SimpleAutoloaderWithCacheFile
     */
    public function setDebugLevel(int $level)
    {
        $this->debugLevel = $level;
        return $this;
    }

    /**
     * Simple autoloader to be use with
     */
    public function autoloader(string $class)
    {
        if (isset($this->classes[$class])) {
            $file = $this->classes[$class];
            if (file_exists($file)) {
                require_once $file;
            } else {
                if ($this->debugLevel == 1) {
                    throw new \Exception('File "' . $file . '" not found !');
                }
            }
        } else {
            if ($this->debugLevel == 1) {
                throw new \Exception('Class "' . $class . '" not found !');
            }
        }
    }
}
