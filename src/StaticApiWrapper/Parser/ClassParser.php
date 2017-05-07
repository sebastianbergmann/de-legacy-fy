<?php
/*
 * This file is part of de-legacy-fy.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\DeLegacyFy;

interface ClassParser
{
    /**
     * @param string      $className
     * @param string      $fileName
     * @param bool|string $bootstrap
     *
     * @return ParsedClass
     */
    public function parse($className, $fileName, $bootstrap);
}
