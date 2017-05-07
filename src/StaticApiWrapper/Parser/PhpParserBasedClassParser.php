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

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;

class PhpParserBasedClassParser implements ClassParser
{
    /**
     * @param string      $className
     * @param string      $fileName
     * @param bool|string $bootstrap
     *
     * @return ParsedClass
     *
     * @throws RuntimeException
     */
    public function parse($className, $fileName, $bootstrap)
    {
        $this->ensureSourceFileExists($fileName);

        $parser = new Parser(new Lexer);

        $nodes = $parser->parse(\file_get_contents($fileName));

        $classNode = $this->getClassNode($className, $nodes);

        $methods = [];

        foreach ($this->getPublicMethodNodes($classNode) as $methodNode) {
            $methods[] = new PublicMethod(
                $methodNode->name,
                $this->getDocblock($methodNode, $className),
                $this->getMethodParameters($methodNode),
                $this->getMethodParameters($methodNode, true)
            );
        }

        return new ParsedClass($className, $methods);
    }

    /**
     * @param string $filename
     *
     * @throws RuntimeException
     */
    private function ensureSourceFileExists($filename)
    {
        if (\file_exists($filename)) {
            return;
        }

        throw new RuntimeException(
            \sprintf(
                'Cannot load source file "%s"',
                $filename
            )
        );
    }

    /**
     * @param string $className
     * @param array  $parsedNodes
     *
     * @return Node\Stmt\Class_
     *
     * @throws RuntimeException
     */
    private function getClassNode($className, array $parsedNodes)
    {
        /** @var Node $node */
        foreach ($parsedNodes as $node) {
            if ($node instanceof Node\Stmt\Class_ && $node->name == $className) {
                return $node;
            }
        }

        throw new RuntimeException(\sprintf('Class %s not found', $className));
    }

    /**
     * @param Node\Stmt\ClassMethod $methodNode
     * @param bool                  $forCall
     *
     * @return string
     */
    private function getMethodParameters(Node\Stmt\ClassMethod $methodNode, $forCall = false)
    {
        $parameters = [];

        foreach ($methodNode->params as $parameter) {
            $name      = '$' . $parameter->name;
            $default   = '';
            $reference = '';
            $typeHint  = '';

            if (!$forCall) {
                if (!empty($parameter->type)) {
                    $typeHint = $parameter->type . ' ';
                }

                if ($parameter->default !== null) {
                    $default = $this->getDefaultValue($parameter);
                }
            }

            if (!$forCall && $parameter->byRef) {
                $reference = '&';
            }

            $parameters[] = $typeHint . $reference . $name . $default;
        }

        return \implode(', ', $parameters);
    }

    /**
     * @param Node\Param $parameter
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function getDefaultValue(Node\Param $parameter)
    {
        if ($parameter->default instanceof Node\Expr\ConstFetch) {
            return ' = ' . $parameter->default->name->toString();
        }

        if ($parameter->default instanceof Node\Expr\Array_) {
            return ' = array()';
        }

        if ($parameter->default instanceof Node\Scalar\String_) {
            return \sprintf(' = \'%s\'', $parameter->default->value);
        }

        if ($parameter->default instanceof Node\Scalar\LNumber) {
            return \sprintf(' = %s', $parameter->default->value);
        }

        if ($parameter->default instanceof Node\Scalar\DNumber) {
            return \sprintf(' = %s', $parameter->default->value);
        }

        throw new RuntimeException(\sprintf('Unsupported default value for parameter %s', $parameter->name));
    }

    /**
     * @param Node\Stmt\ClassMethod $method
     * @param $classname
     *
     * @return string
     */
    private function getDocblock(Node\Stmt\ClassMethod $method, $classname)
    {
        $parser = new DocBlockParser;

        return $parser->parse($method->getDocComment(), $classname, $method->name);
    }

    /**
     * @param Node\Stmt\Class_ $classNode
     *
     * @return Node\Stmt\ClassMethod[]
     */
    private function getPublicMethodNodes(Node\Stmt\Class_ $classNode)
    {
        $methodNodes = [];

        foreach ($classNode->stmts as $node) {
            if ($node instanceof Node\Stmt\ClassMethod && $node->isPublic()) {
                $methodNodes[] = $node;
            }
        }

        return $methodNodes;
    }
}
