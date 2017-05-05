<?php

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
    private $publicMethods = array();

    /**
     * @param string $name
     * @param PublicMethod[] $publicMethods
     */
    public function __construct($name, array $publicMethods)
    {
        $this->name = $name;
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
