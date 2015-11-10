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
     */
    public function setUp()
    {
        /*
         * No autoloader for the tests, so we use require.
         */
        require_once ROOT_PATH . '/src/SimpleAutoloader/CacheGenerator.php';
    }

    /**
     * Test __construct method
     */
    public function testConstructMethod()
    {
        $cacheGenerator = new CacheGenerator([]);
        $errors = $cacheGenerator->getErrors();
        $this->assertCount(1, $errors);
        
        $expectedErrors = [
            'A directory is needed!'
        ];

        $this->assertSame($expectedErrors, $errors);
    }
}
