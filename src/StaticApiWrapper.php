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

use ReflectionClass;
use ReflectionMethod;

class StaticApiWrapper
{
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
     * @param string      $originalClass
     * @param string      $originalFile
     * @param string      $wrapperClass
     * @param string      $wrapperFile
     * @param bool|string $bootstrap
     */
    public function generate($originalClass, $originalFile, $wrapperClass, $wrapperFile, $bootstrap)
    {
        if (\is_string($bootstrap)) {
            $this->loadBootstrap($bootstrap);
        }

        $this->loadClass($originalClass, $originalFile);

        $rc = new ReflectionClass($originalClass);

        $buffer = \sprintf(
            self::$classTemplate,
            $originalClass,
            $originalClass,
            $wrapperClass
        );

        foreach ($rc->getMethods() as $method) {
            if ($method->isPublic() /*&& $method->isStatic()*/) {
                $buffer .= \sprintf(
                    self::$methodTemplate,
                    $this->getDocblock($method),
                    $method->getName(),
                    $this->getMethodParameters($method),
                    $originalClass,
                    $method->getName(),
                    $this->getMethodParameters($method, true)
                );
            }
        }

        $buffer .= "}\n";

        \file_put_contents($wrapperFile, $buffer);
    }

    /**
     * @param string $bootstrap
     *
     * @throws RuntimeException
     */
    private function loadBootstrap($bootstrap)
    {
        if (!\file_exists($bootstrap)) {
            throw new RuntimeException(
                \sprintf(
                    'Cannot load bootstrap script "%s"',
                    $bootstrap
                )
            );
        }

        require $bootstrap;
    }

    /**
     * @param string $class
     * @param string $file
     *
     * @throws RuntimeException
     */
    private function loadClass($class, $file)
    {
        if (!\file_exists($file)) {
            throw new RuntimeException(
                \sprintf(
                    'Cannot load source file "%s"',
                    $file
                )
            );
        }

        require $file;

        if (!\class_exists($class, false)) {
            throw new RuntimeException(
                \sprintf(
                    'Class "%s" does not exist',
                    $class
                )
            );
        }
    }

    /**
     * @param ReflectionMethod $method
     *
     * @return string
     */
    private function getDocblock(ReflectionMethod $method)
    {
        $docblock    = \substr($method->getDocComment(), 3, -2);
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
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        return $docblock;
    }

    /**
     * @param ReflectionMethod $method
     * @param bool             $forCall
     *
     * @return string
     */
    private function getMethodParameters(ReflectionMethod $method, $forCall = false)
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $name      = '$' . $parameter->getName();
            $default   = '';
            $reference = '';
            $typeHint  = '';

            if (!$forCall) {
                if ($parameter->isArray()) {
                    $typeHint = 'array ';
                } elseif ($parameter->isCallable()) {
                    $typeHint = 'callable ';
                } else {
                    $class = $parameter->getClass();

                    if ($class !== null) {
                        $typeHint = $class->getName() . ' ';
                    }
                }

                if ($parameter->isDefaultValueAvailable()) {
                    $default = ' = ' . \str_replace(
                        "array (\n",
                        'array(',
                        \var_export($parameter->getDefaultValue(), true)
                    );
                } elseif ($parameter->isOptional()) {
                    $default = ' = null';
                }
            }

            if (!$forCall && $parameter->isPassedByReference()) {
                $reference = '&';
            }

            $parameters[] = $typeHint . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
    }
}
