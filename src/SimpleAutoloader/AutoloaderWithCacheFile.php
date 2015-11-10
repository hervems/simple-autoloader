<?php
/**
 * This file is part of Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @license   LGPL, please view the LICENSE file.
 * @author    Hervé Seignole <herve.seignole@gmail.com>
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
     * @param  array $classes List of classes.
     * @return AutoloaderWithCacheFile Provide a fluid interface.
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
     * @param  int $level Level of debug.
     * @return SimpleAutoloaderWithCacheFile Provide a fluid interface.
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
     * Simple autoloader to be use with.
     *
     * @param string $class A class that we want to find.
     * @throws \Exception If you are in debugLevel one (File or Class not found).
     */
    public function autoloader(string $class)
    {
        if (isset($this->classes[$class])) {
            $file = $this->classes[$class];
            if (file_exists($file)) {
                try {
                    require_once $file;
                } catch (\Throwable $exception) {
                    throw new \Exception('Parse error for "' . $file . '"!');
                }
            } else {
                if ($this->debugLevel == 1) {
                    throw new \Exception('File "' . $file . '" not found!');
                }
            }
        } else {
            if ($this->debugLevel == 1) {
                throw new \Exception('Class "' . $class . '" not found!');
            }
        }
    }
}
