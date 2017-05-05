<?php

namespace SebastianBergmann\DeLegacyFy;


/**
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link      http://github.com/sebastianbergmann/de-legacy-fy/tree
 * @since     Class available since Release 1.0.0
 */
interface StaticApiWrapper
{
    /**
     * @param string $originalClass
     * @param string $originalFile
     * @param string $wrapperClass
     * @param string $wrapperFile
     * @param boolean|string $bootstrap
     */
    public function generate($originalClass, $originalFile, $wrapperClass, $wrapperFile, $bootstrap);
}
