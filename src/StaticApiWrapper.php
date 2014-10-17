<?php
/**
 * de-legacy-fy
 *
 * Copyright (c) 2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   de-legacy-fy
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

namespace SebastianBergmann\DeLegacyFy;

use ReflectionClass;
use ReflectionMethod;

/**
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link      http://github.com/sebastianbergmann/de-legacy-fy/tree
 * @since     Class available since Release 1.0.0
 */
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
     * @param string         $originalClass
     * @param string         $originalFile
     * @param string         $wrapperClass
     * @param string         $wrapperFile
     * @param boolean|string $bootstrap
     */
    public function generate($originalClass, $originalFile, $wrapperClass, $wrapperFile, $bootstrap)
    {
        if (is_string($bootstrap)) {
            $this->loadBootstrap($bootstrap);
        }

        $this->loadClass($originalClass, $originalFile);

        $rc = new ReflectionClass($originalClass);

        $buffer = sprintf(
            self::$classTemplate,
            $originalClass,
            $originalClass,
            $wrapperClass
        );

        foreach ($rc->getMethods() as $method) {
            if ($method->isPublic() /*&& $method->isStatic()*/) {
                $buffer .= sprintf(
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

        file_put_contents($wrapperFile, $buffer);
    }

    /**
     * @param  string $bootstrap
     * @throws RuntimeException
     */
    private function loadBootstrap($bootstrap)
    {
        if (!file_exists($bootstrap)) {
            throw new RuntimeException(
                sprintf(
                    'Cannot load bootstrap script "%s"',
                    $bootstrap
                )
            );
        }

        require $bootstrap;
    }

    /**
     * @param  string $class
     * @param  string $file
     * @throws RuntimeException
     */
    private function loadClass($class, $file)
    {
        if (!file_exists($file)) {
            throw new RuntimeException(
                sprintf(
                    'Cannot load source file "%s"',
                    $file
                )
            );
        }

        require $file;

        if (!class_exists($class, false)) {
            throw new RuntimeException(
                sprintf(
                    'Class "%s" does not exist',
                    $class
                )
            );
        }
    }

    /**
     * @param  ReflectionMethod $method
     * @return string
     */
    private function getDocblock(ReflectionMethod $method)
    {
        $docblock    = substr($method->getDocComment(), 3, -2);
        $annotations = array('param' => array(), 'throws' => array());

        if (preg_match_all('/@(?P<name>[A-Za-z_-]+)(?:[ \t]+(?P<value>.*?))?[ \t]*\r?$/m', $docblock, $matches)) {
            $numMatches = count($matches[0]);

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

        $docblock .= sprintf(
            "\n     * @see %s::%s\n     */",
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );

        return $docblock;
    }

    /**
     * @param  ReflectionMethod $method
     * @param  boolean          $forCall
     * @return string
     */
    private function getMethodParameters(ReflectionMethod $method, $forCall = false)
    {
        $parameters = array();

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
                    $default = ' = ' . str_replace(
                        "array (\n",
                        'array(',
                        var_export($parameter->getDefaultValue(), true)
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

        return join(', ', $parameters);
    }
}
