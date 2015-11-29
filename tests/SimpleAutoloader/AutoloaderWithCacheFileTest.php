<?php
/**
 * This file is part of Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @license   LGPL, please view the LICENSE file.
 * @author    Hervé Seignole <herve.seignole@gmail.com>
 */

declare(strict_types=1);

namespace SimpleAutoloader\Tests;

use SimpleAutoloader\AutoloaderWithCacheFile;

/**
 * Test of AutoloaderWithCacheFile class
 */
class AutoloaderWithCacheFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Load Class AutoloaderWithCacheFile.
     *
     * @return void
     */
    public function setUp()
    {
        /*
         * No autoloader for the tests, so we use require.
         */
        require_once ROOT_PATH . '/src/SimpleAutoloader/AutoloaderWithCacheFile.php';
    }

    /**
     * Test setter and getter of classes array.
     *
     * @return void
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
     * Test setter and getter of debug level.
     *
     * @return void
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
     * Test autoloader method in classic way with a class exists.
     *
     * @return void
     */
    public function testAutoloaderMethodInClassicWay()
    {
        $autoload = new AutoloaderWithCacheFile();

        $classes = [
            'ClassOfTestOne\\ClassOfTestOne' => __DIR__ . '/_files/ClassOfTestOne.php'
        ];

        $autoload->setClasses($classes)
            ->setDebugLevel(1);

        $autoload->autoloader('ClassOfTestOne\\ClassOfTestOne');

        $class = new \ClassOfTestOne\ClassOfTestOne();
        $this->assertInstanceOf('ClassOfTestOne\\ClassOfTestOne', $class);
        $this->assertSame('ClassOfTestOne\\ClassOfTestOne', $class->getName());
    }

    /**
     * Test autoloader method with a non-existent class.
     *
     * @expectedException Exception
     * @expectedExceptionMessage Class "ClassDoesntExists" not found!
     * @return void
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
     * @expectedExceptionMessage File "_files/non-existent-file.php" not found!
     * @return void
     */
    public function testAutoloaderMethodWithANonExistentFile()
    {
        $autoload = new AutoloaderWithCacheFile();

        $classes = [
            'ClassOfTestOne' => '_files/non-existent-file.php'
        ];

        $autoload->setClasses($classes)
            ->setDebugLevel(1);

        $autoload->autoloader('ClassOfTestOne');
    }

    /**
     * Test autoloader method with a bad file (parse error)!
     *
     * @expectedException Exception
     * @expectedExceptionMessageRegExp /Parse error for "[^"]*_files\/bad-file\.php" \[syntax error, unexpected '1' \(T_LNUMBER\)\]!$/
     * @return void
     */
    public function testAutoloaderMethodWithABadFile()
    {
        $autoload = new AutoloaderWithCacheFile();

        $classes = [
            'ClassOfTestOne' => __DIR__ . '/_files/bad-file.php'
        ];

        $autoload->setClasses($classes)
            ->setDebugLevel(1);

        $autoload->autoloader('ClassOfTestOne');
    }
}
