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
     * Load Class CacheGenerator and clean some files.
     *
     * @return void
     */
    public function setUp()
    {
        /*
         * No autoloader for the tests, so we use require.
         */
        require_once ROOT_PATH . '/src/SimpleAutoloader/CacheGenerator.php';

        $tmpDirectory = sys_get_temp_dir();

        /* Clean classes.php.cache if present */
        if (file_exists($tmpDirectory . '/classes.php.cache')) {
            unlink($tmpDirectory . '/classes.php.cache');
        }

        /* 
         * Clean directory not readable for
         * testUnexpectedValueExceptionCatchInRunMethod
         */
        $directoryNotReadable = $tmpDirectory .
            '/SimpleAutoloader.testUnexpectedValueExceptionCatchInRunMethod';

        if (is_dir($directoryNotReadable)) {
            chmod($directoryNotReadable, 0777);
            rmdir($directoryNotReadable);
        }

        /* 
         * Clean file not readable for
         * testParseFileMethodReturnsErrorWhenAFileIsNotReadable
         */
        $fileNotReadable = $tmpDirectory .
            '/SimpleAutoloader.testParseFileMethodReturnsErrorWhenAFileIsNotReadable';

        if (file_exists($fileNotReadable)) {
            chmod($fileNotReadable, 0777);
            unlink($fileNotReadable);
        }
    }

    /**
     * Clean some files.
     *
     * @return void
     */
    public function tearDown()
    {
        $tmpDirectory = sys_get_temp_dir();

        /* Clean classes.php.cache if present */
        if (file_exists($tmpDirectory . '/classes.php.cache')) {
            unlink($tmpDirectory . '/classes.php.cache');
        }

        /* 
         * Clean directory not readable for
         * testUnexpectedValueExceptionCatchInRunMethod
         */
        $directoryNotReadable = $tmpDirectory .
            '/SimpleAutoloader.testUnexpectedValueExceptionCatchInRunMethod';

        if (is_dir($directoryNotReadable)) {
            chmod($directoryNotReadable, 0777);
            rmdir($directoryNotReadable);
        }

        /* 
         * Clean file not readable for
         * testParseFileMethodReturnsErrorWhenAFileIsNotReadable
         */
        $fileNotReadable = $tmpDirectory .
            '/SimpleAutoloader.testParseFileMethodReturnsErrorWhenAFileIsNotReadable';

        if (file_exists($fileNotReadable)) {
            chmod($fileNotReadable, 0777);
            unlink($fileNotReadable);
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

    public function testRunMethodWithTwoSameClassesInDifferentFile()
    {
        $args = [
            0 => 'program'
        ];

        $cacheGenerator = new CacheGenerator();

        $files = [
            'file1.php' => '<?php class A {}' . "\n" .
                'interface B {}' . "\n" .
                'trait C {}' . "\n",
            'file2.php' => '<?php class B {}' . "\n" .
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

        $this->assertSame(
            ['class "B" already load [conflict]'],
            $errors
        );
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
     * Test setArgs method with bad parameters number.
     *
     * @return void
     */
    public function testSetArgsMethodWithBadParametersNumber()
    {
        $cacheGenerator = new CacheGenerator();

        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/empty-directory',
            2 => '--filename'
        ];

        $errors = $cacheGenerator->run($args);

        $this->assertSame(
            ['Option needs a value!'],
            $errors
        );
    }

    /**
     * Test setArgs method with bad parameters.
     *
     * @return void
     */
    public function testSetArgsMethodWithBadParameters()
    {
        $cacheGenerator = new CacheGenerator();

        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/empty-directory',
            2 => '--foo',
            3 => 'bar'
        ];

        $errors = $cacheGenerator->run($args);

        $this->assertSame(
            [
                'Option "--foo" is not valid!'
            ],
            $errors
        );
    }

    /**
     * Test setArgs method with an empty value parameter.
     *
     * @return void
     */
    public function testSetArgsMethodWithAnEmptyValueParameter()
    {
        $cacheGenerator = new CacheGenerator();

        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/empty-directory',
            2 => '--filename',
            3 => ''
        ];

        $errors = $cacheGenerator->run($args);

        $this->assertSame(
            [
                'Option value of "--filename" is empty!'
            ],
            $errors
        );
    }

    /**
     * Test setArgs method with a good value parameter.
     *
     * @return void
     */
    public function testSetArgsMethodWithAGoodValueParameter()
    {
        $cacheGenerator = new CacheGenerator();

        $args = [
            0 => 'program',
            1 => __DIR__ . '/_files/empty-directory',
            2 => '--filename',
            3 => 'myfile.php'
        ];

        $errors = $cacheGenerator->setArgs($args);

        $this->assertCount(0, $errors);

        $this->assertSame(
            'myfile.php',
            $cacheGenerator->getArgs($args)['options']['--filename']['value']
        );
    }

    /**
     * Test UnexpectedValueException catch in run method.
     *
     * @return void
     */
    public function testUnexpectedValueExceptionCatchInRunMethod()
    {
        $cacheGenerator = new CacheGenerator();

        $tmpDirectory = sys_get_temp_dir();

        $directoryNotReadable = $tmpDirectory .
            '/SimpleAutoloader.testUnexpectedValueExceptionCatchInRunMethod';

        if (is_dir($directoryNotReadable)) {
            chmod($directoryNotReadable, 0777);
            rmdir($directoryNotReadable);
        }

        mkdir($directoryNotReadable, 0);

        $args = [
            0 => 'program',
            1 => $directoryNotReadable
        ];

        $errors = $cacheGenerator->run($args);

        $this->assertSame(
            'RecursiveDirectoryIterator::__construct(' .
            $tmpDirectory . DIRECTORY_SEPARATOR .
            'SimpleAutoloader.testUnexpectedValueExceptionCatchInRunMethod): ' .
            'failed to open dir: Permission denied',
            $errors[0]
        );
    }

    /**
     * Test parseFile method returns error when a file does not exists.
     *
     * @return void
     */
    public function testParseFileMethodReturnsErrorWhenAFileDoesNotExists()
    {
        $cacheGenerator = new CacheGenerator();

        $error = $cacheGenerator->parseFile(
            __DIR__ . '/_files/no-file.php',
            [],
            false
        )['errors'][0];

        $expectedError = 'file_get_contents(' .
            __DIR__ .
            '/_files/no-file.php): failed to open stream: No such file or directory';

        $this->assertSame($expectedError, $error);
    }

    /**
     * Test parseFile method returns error when a file does not exists.
     *
     * @return void
     */
    public function testParseFileMethodReturnsErrorWhenAFileIsNotReadable()
    {
        $cacheGenerator = new CacheGenerator();

        $tmpDirectory = sys_get_temp_dir();

        $fileNotReadable = $tmpDirectory .
            '/SimpleAutoloader.testParseFileMethodReturnsErrorWhenAFileIsNotReadable.php';

        if (file_exists($fileNotReadable)) {
            chmod($fileNotReadable, 0777);
            unlink($fileNotReadable);
        }

        file_put_contents($fileNotReadable, '');
        chmod($fileNotReadable, 0);

        $error = $cacheGenerator->parseFile(
            $tmpDirectory .
            '/SimpleAutoloader.testParseFileMethodReturnsErrorWhenAFileIsNotReadable.php',
            [],
            false
        )['errors'][0];

        $expectedError = 'file_get_contents(' .
            $fileNotReadable .
            '): failed to open stream: Permission denied';

        $this->assertSame($expectedError, $error);
    }

    /**
     * Test run method with a specific filename
     *
     * @return void
     */
    public function testRunMethodWithASpecificFile()
    {
        $cacheGenerator = new CacheGenerator();

        $classCacheFilename = __DIR__ . '/_files/bad-cache.php.cache';

        $args = [
            'program',
            __DIR__ . '/_files/non-empty-directory',
            '--filename',
            $classCacheFilename
        ];

        $errors = $cacheGenerator->run($args);

        $this->assertCount(1, $errors);
        $this->assertSame(
            ['Parse error when including "' . $classCacheFilename . '"'],
            $errors
        );
    }
}
