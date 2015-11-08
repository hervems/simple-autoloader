<?php
/**
 * This file is part of Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @license   LGPL, please view the LICENSE file.
 */

declare(strict_types=1);

/*
 * No autoloader for the tests, so we use require.
 */
require_once ROOT_PATH . '/src/SimpleAutoloader/AutoloaderWithCacheFile.php';

use SimpleAutoloader\AutoloaderWithCacheFile;

/**
 * Test of AutoloaderWithCacheFile class
 */
class AutoloaderWithCacheFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test setter and getter of classes array
     */
    public function testSetGetClassesMethods()
    {
        $autoload = new AutoloaderWithCacheFile();
    
        $classes = [
            'Class1' => '/tmp/Class1.php'
        ];

        /* Test the fluid interface of setter method */
        $this->assertInstanceOf(
            'SimpleAutoloader\AutoloaderWithCacheFile',
            $autoload->setClasses($classes)
        );

        $this->assertSame($classes, $autoload->getClasses());
    }

    /**
     * Test setter and getter of debug level
     */
    public function testSetGetDebugLevelMethods()
    {
        $autoload = new AutoloaderWithCacheFile();
    
        $debugLevel = 1;

        /* Test the fluid interface of setter method */
        $this->assertInstanceOf(
            'SimpleAutoloader\AutoloaderWithCacheFile',
            $autoload->setDebugLevel($debugLevel)
        );

        $this->assertSame($debugLevel, $autoload->getDebugLevel());
    }

    /**
     * Test autoloader method in classic way
     * with a class exists!
     */
    public function testAutoloaderMethodInClassicWay()
    {
        $autoload = new AutoloaderWithCacheFile();

        $classes = [
            'ClassOfTestOne' => __DIR__ . '/_files/ClassOfTestOne.php'   
        ];

        $autoload->setClasses($classes)
            ->setDebugLevel(1);

        $autoload->autoloader('ClassOfTestOne');

        $class = new ClassOfTestOne();
        $this->assertInstanceOf('ClassOfTestOne', $class);
        $this->assertSame('ClassOfTestOne', $class->getName());
    }

    /**
     * Test autoloader method with a non-existent class!
     *
     * @expectedException Exception
     * @expectedMessage Class "ClassDoesntExists" not found!
     */
    public function testAutoloaderMethodWithANonExistentClass()
    {
        $autoload = new AutoloaderWithCacheFile();

        $classes = [
            'ClassOfTestOne' => __DIR__ . '/_files/ClassOfTestOne.php'   
        ];

        $autoload->setClasses($classes)
            ->setDebugLevel(1);

        $autoload->autoloader('ClassDoesntExists');
    }

    /**
     * Test autoloader method with a non-existent file!
     *
     * @expectedException Exception
     * @expectedMessage Class "_files/bad-file.php" not found!
     */
    public function testAutoloaderMethodWithANonExistentFile()
    {
        $autoload = new AutoloaderWithCacheFile();

        $classes = [
            'ClassOfTestOne' => '_files/bad-file.php'   
        ];

        $autoload->setClasses($classes)
            ->setDebugLevel(1);

        $autoload->autoloader('ClassOfTestOne');
    }
}
