<?php
/**
 * This file is part of Simple Autoloader
 *
 * @copyright Copyright (c) 2015 Hervé Seignole (herve.seignole@gmail.com)
 * @license   LGPL, please view the LICENSE file.
 * @author    Hervé Seignole <herve.seignole@gmail.com>
 */

namespace ClassOfTestOne;

/**
 * A simpe class for AutoloaderWithCacheFile test.
 */
class ClassOfTestOne
{
    /**
     * Get name of the class.
     *
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }
}
