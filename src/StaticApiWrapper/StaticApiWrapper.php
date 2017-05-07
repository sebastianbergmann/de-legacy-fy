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

class StaticApiWrapper
{
    /**
     * @var ClassParser
     */
    private $parser;

    /**
     * @var string
     */
    private static $classTemplate = '<?php
/**
 * Automatically generated wrapper class for %s
 * @see %s
 */
class %s
{';

    /**
     * @var string
     */
    private static $methodTemplate = '%s
    public function %s(%s)
    {
        return %s::%s(%s);
    }
';

    /**
     * @param ClassParser $parser
     */
    public function __construct(ClassParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param string      $originalClass
     * @param string      $originalFile
     * @param string      $wrapperClass
     * @param string      $wrapperFile
     * @param bool|string $bootstrap
     */
    public function generate($originalClass, $originalFile, $wrapperClass, $wrapperFile, $bootstrap)
    {
        $class = $this->parser->parse($originalClass, $originalFile, $bootstrap);

        $buffer = \sprintf(
            self::$classTemplate,
            $originalClass,
            $originalClass,
            $wrapperClass
        );

        foreach ($class->getPublicMethods() as $method) {
            $buffer .= \sprintf(
                self::$methodTemplate,
                $method->getDocBlock(),
                $method->getName(),
                $method->getParameters(),
                $originalClass,
                $method->getName(),
                $method->getCallParameters()
            );
        }

        $buffer .= "}\n";

        \file_put_contents($wrapperFile, $buffer);
    }
}
