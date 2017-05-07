<?php

namespace SebastianBergmann\DeLegacyFy;

class DocBlockParser
{
    /**
     * @param string $originalDocBlock
     * @param string $classname
     * @param string $methodname
     * @return string
     */
    public function parse($originalDocBlock, $classname, $methodname)
    {
        $docblock    = substr($originalDocBlock, 3, -2);
        $annotations = ['param' => [], 'throws' => []];

        if (\preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docblock, $matches)) {
            $numMatches = \count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                $annotations[$matches['name'][$i]][] = $matches['value'][$i];
            }
        }

        $docblock = "\n    /**";

        foreach ($annotations['param'] as $param) {
            $docblock .= "\n     * @param " . $param;
        }

        if (isset($annotations['return'])) {
            $docblock .= "\n     * @return " . $annotations['return'][0];
        }

        foreach ($annotations['throws'] as $throws) {
            $docblock .= "\n     * @throws " . $throws;
        }

        $docblock .= \sprintf(
            "\n     * @see %s::%s\n     */",
            $classname,
            $methodname
        );

        return $docblock;
    }
}
