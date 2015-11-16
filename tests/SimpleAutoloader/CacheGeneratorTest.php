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

use SimpleAutoloader\CacheGenerator;

/**
 * Test of AutoloaderWithCacheFile class
 */
class CacheGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Load Class CacheGenerator.
     *
     * @return void
     */
    public function setUp()
    {
        /*
         * No autoloader for the tests, so we use require.
         */
        require_once ROOT_PATH . '/src/SimpleAutoloader/CacheGenerator.php';

        /* clean classes.php.cache if present */
        if (file_exists(__DIR__ . '/classes.php.cache')) {
            unlink(__DIR__ . '/classes.php.cache');
        }
    }

    /**
     * Clean classes.php.cache file.
     *
     * @return void
     */
    public function tearDown()
    {
        /* clean classes.php.cache if present */
        if (file_exists(__DIR__ . '/classes.php.cache')) {
            unlink(__DIR__ . '/classes.php.cache');
        }
    }

    /**
     * Test run method with zero arg.
     *
     * @return void
     */
    public function testRunMethodWithZeroArg()
    {
        $cacheGenerator = new CacheGenerator();
        $errors = $cacheGenerator->run([]);
        $this->assertCount(1, $errors);
        
        $expectedErrors = [
            'A directory is needed!'
        ];

        $this->assertSame($expectedErrors, $errors);
    }

    /**
     * Test run method with a nonexistent directory.
     *
     * @return void
     */
    public function testRunMethodWithANonExistentDirectory()
    {
        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/non-existent-directory'
        ];

        $cacheGenerator = new CacheGenerator();

        $errors = $cacheGenerator->run($args);

        $this->assertCount(1, $errors);

        $this->assertSame('Directory "' . $args[1] . '" does not exists!', $errors[0]);

        $errorsViaGetErrors = $cacheGenerator->getErrors();
        $this->assertCount(1, $errorsViaGetErrors);
        $this->assertSame($errorsViaGetErrors, $errors);
    }

    /**
     * Test run method with an empty directory.
     *
     * @return void
     */
    public function testRunMethodWithAnEmptyDirectory()
    {
        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/empty-directory'
        ];

        $cacheGenerator = new CacheGenerator();

        /* Use array write context */
        $cacheGenerator->setWriteContext('array');

        $errors = $cacheGenerator->run($args);

        $this->assertFalse($cacheGenerator->hasFoundClasses());

        $this->assertCount(0, $cacheGenerator->getClassesCache());
    }

    /**
     * Test run method with an non-empty directory.
     *
     * @return void
     */
    public function testRunMethodWithAnNonEmptyDirectory()
    {
        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/non-empty-directory'
        ];

        $cacheGenerator = new CacheGenerator();

        /* Use array write context */
        $cacheGenerator->setWriteContext('array');

        $errors = $cacheGenerator->run($args);

        $this->assertTrue($cacheGenerator->hasFoundClasses());

        $this->assertCount(12, $cacheGenerator->getClassesCache());

        $classesCache = $cacheGenerator->getClassesCache();

        $classes = array_keys($classesCache);
        sort($classes);

        $classesExpected = [
            'ClassOfTestOne\\ClassOfTestOne1',
            'ClassOfTestOne\\ClassOfTestOne2',
            'ClassOfTestOne\\Interface1',
            'ClassOfTestOne\\Interface2',
            'ClassOfTestOne\\Trait1',
            'ClassOfTestOne\\Trait2',
            'Class_Of_Test_One1',
            'Class_Of_Test_One2',
            'Interface2',
            'Interface_1',
            'Trait2',
            'Trait_1'
        ];

        $this->assertSame($classesExpected, $classes);
    }

    /**
     * Test run method with an non-empty virtual directory.
     * Via read context and files array.
     *
     * @return void
     */
    public function testRunMethodWithAnNonEmptyVirtualDirectory()
    {
        $args = [
            0 => 'program'
        ];

        $cacheGenerator = new CacheGenerator();

        $files = [
            'file1.php' => '<?php class A {}' . "\n" .
                'interface B {}' . "\n" .
                'trait C {}' . "\n",
            'file2.php' => '<?php namespace A;' . "\n" .
                'class A {}' . "\n" .
                'interface C {}' . "\n" .
                'trait D {}' . "\n"
        ];

        /* Use array read context */
        $cacheGenerator->setReadContext('array')
            ->setFiles($files);

        /* Use array write context */
        $cacheGenerator->setWriteContext('array');

        $errors = $cacheGenerator->run($args);

        $this->assertTrue($cacheGenerator->hasFoundClasses());

        $this->assertCount(6, $cacheGenerator->getClassesCache());

        $classesCache = $cacheGenerator->getClassesCache();

        ksort($classesCache);

        $classesExpected = [
            'A' => 'file1.php',
            'A\\A' => 'file2.php',
            'A\\C' => 'file2.php',
            'A\\D' => 'file2.php',
            'B' => 'file1.php',
            'C' => 'file1.php'
        ];

        $this->assertSame($classesExpected, $classesCache);
    }

    /**
     * Test help method with zero arg.
     *
     * @return void
     */
    public function testHelpMethodWithZeroArg()
    {
        $cacheGenerator = new CacheGenerator();
        $errors = $cacheGenerator->run([]);
        $this->assertCount(1, $errors);

        $errorsViaGetErrors = $cacheGenerator->getErrors();
        $this->assertCount(1, $errorsViaGetErrors);
        $this->assertSame($errorsViaGetErrors, $errors);

        /* Check the help message */
        $message = $cacheGenerator->help($errors);

        $this->assertStringStartsWith('autoload-cache-generator (', $message);

        /* Check error line, here "Directory is needed" */
        $messageLines = explode(PHP_EOL, $message);

        $this->assertContains('Errors:', $messageLines);

        $this->assertContains('  0. A directory is needed!', $messageLines);
    }

    /**
     * Test setArgs method with bad parameters
     *
     * @return void
     */
    public function testSetArgsMethodWithBadParameters()
    {
        $cacheGenerator = new CacheGenerator();

        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/empty-directory'
        ];

        $errors = $cacheGenerator->run([]);
    }
}
