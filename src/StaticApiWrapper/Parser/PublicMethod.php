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

class PublicMethod
{
    private $name;

    private $docBlock;

    private $parameters;

    private $callParameters;

    public function __construct($name, $docBlock, $parameters, $callParameters)
    {
        $this->name           = $name;
        $this->docBlock       = $docBlock;
        $this->parameters     = $parameters;
        $this->callParameters = $callParameters;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDocBlock()
    {
        return $this->docBlock;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getCallParameters()
    {
        return $this->callParameters;
    }
}
