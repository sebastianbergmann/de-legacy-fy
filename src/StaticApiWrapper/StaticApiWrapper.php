<?php
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
     * @param string $originalClass
     * @param string $originalFile
     * @param string $wrapperClass
     * @param string $wrapperFile
     * @param boolean|string $bootstrap
     */
    public function generate($originalClass, $originalFile, $wrapperClass, $wrapperFile, $bootstrap)
    {
        $class = $this->parser->parse($originalClass, $originalFile, $bootstrap);

        $buffer = sprintf(
            self::$classTemplate,
            $originalClass,
            $originalClass,
            $wrapperClass
        );

        foreach ($class->getPublicMethods() as $publicMethod) {
            $buffer .= sprintf(
                self::$methodTemplate,
                $publicMethod->getDocBlock(),
                $publicMethod->getName(),
                $publicMethod->getParameters(),
                $originalClass,
                $publicMethod->getName(),
                $publicMethod->getCallParameters()
            );
        }

        $buffer .= "}\n";

        file_put_contents($wrapperFile, $buffer);
    }



}
