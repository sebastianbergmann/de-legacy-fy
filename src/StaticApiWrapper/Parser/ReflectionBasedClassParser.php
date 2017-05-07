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

class ReflectionBasedClassParser implements ClassParser
{
    /**
     * @param string      $className
     * @param string      $fileName
     * @param bool|string $bootstrap
     *
     * @return ParsedClass
     */
    public function parse($className, $fileName, $bootstrap)
    {
        if (\is_string($bootstrap)) {
            $this->loadBootstrap($bootstrap);
        }

        $this->loadClass($className, $fileName);

        $rc = new ReflectionClass($className);

        $methods = [];

        foreach ($rc->getMethods() as $method) {
            if (!$method->isPublic()) {
                continue;
            }

            $methods[] = new PublicMethod(
                $method->getName(),
                $this->getDocblock($method),
                $this->getMethodParameters($method),
                $this->getMethodParameters($method, true)
            );
        }

        return new ParsedClass($className, $methods);
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
        $parser = new DocBlockParser();

        return $parser->parse($method->getDocComment(), $method->getDeclaringClass()->getName(), $method->getName());
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
            $name           = '$' . $parameter->getName();
            $default        = '';
            $reference      = '';
            $typeAnnotation = '';

            if (!$forCall) {
                if ($parameter->isArray()) {
                    $typeAnnotation = 'array ';
                } elseif ($parameter->isCallable()) {
                    $typeAnnotation = 'callable ';
                } else {
                    $class = $parameter->getClass();

                    if ($class !== null) {
                        $typeAnnotation = $class->getName() . ' ';
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

            $parameters[] = $typeAnnotation . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
    }
}
