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

class ParsedClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var PublicMethod[]
     */
    private $publicMethods = [];

    /**
     * @param string         $name
     * @param PublicMethod[] $publicMethods
     */
    public function __construct($name, array $publicMethods)
    {
        $this->name          = $name;
        $this->publicMethods = $publicMethods;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PublicMethod[]
     */
    public function getPublicMethods()
    {
        return $this->publicMethods;
    }
}
