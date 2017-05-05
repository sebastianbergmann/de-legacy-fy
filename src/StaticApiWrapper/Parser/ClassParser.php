<?php
namespace SebastianBergmann\DeLegacyFy;

interface ClassParser
{
    /**
     * @param string $classname
     * @param string $filename
     * @param boolean|string $bootstrap
     * @return ParsedClass
     */
    public function parse($classname, $filename, $bootstrap);
}
