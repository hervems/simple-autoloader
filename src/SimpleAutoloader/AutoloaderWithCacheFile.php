<?php
/**
 * This file is part of Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @license   LGPL, please view the LICENSE file.
 */

declare(strict_types=1);

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
     * Provide a fluid interface.
     *
     * @param  array $classes
     * @return AutoloaderWithCacheFile
     */
    public function setClasses(array $classes): AutoloaderWithCacheFile
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * Get classes cache array.
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Set level of debug.
     * Provide a fluid interface.
     *
     * @param  int $level Level of debug
     * @return SimpleAutoloaderWithCacheFile
     */
    public function setDebugLevel(int $level): AutoloaderWithCacheFile
    {
        $this->debugLevel = $level;
        return $this;
    }

    /**
     * Get debug level.
     *
     * @return int
     */
    public function getDebugLevel(): int
    {
        return $this->debugLevel;
    }

    /**
     * Simple autoloader to be use with
     *
     * @throw Exception
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
