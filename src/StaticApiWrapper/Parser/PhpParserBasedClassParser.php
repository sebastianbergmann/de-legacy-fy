<?php
namespace SebastianBergmann\DeLegacyFy;

use PhpParser\Lexer;
use PhpParser\Node;
use PhpParser\Parser;

class PhpParserBasedClassParser implements ClassParser
{
    /**
     * @param string $classname
     * @param string $filename
     * @param bool|string $bootstrap
     * @return ParsedClass
     */
    public function parse($classname, $filename, $bootstrap)
    {
        $this->ensureClassFileExists($filename);

        $parser = new Parser(new Lexer());

        $nodes = $parser->parse(file_get_contents($filename));

        $classNode = $this->getClassNode($classname, $nodes);

        $methods = array();

        foreach ($this->getPublicMethodNodes($classNode) as $methodNode) {
            $methods[] = new PublicMethod(
                $methodNode->name,
                $this->getDocblock($methodNode, $classname),
                $this->getMethodParameters($methodNode),
                $this->getMethodParameters($methodNode, true)
            );
        }

        return new ParsedClass($classname, $methods);
    }

    /**
     * @param string $filename
     */
    private function ensureClassFileExists($filename)
    {
        if (file_exists($filename)) {
            return;
        }
        throw new RuntimeException(
            sprintf(
                'Cannot load source file "%s"',
                $filename
            )
        );
    }

    /**
     * @param $classname
     * @param array $parsedNodes
     * @return Node\Stmt\Class_
     */
    private function getClassNode($classname, array $parsedNodes)
    {
        /** @var Node $node */
        foreach ($parsedNodes as $node) {
            if ($node instanceof Node\Stmt\Class_ && $node->name == $classname) {
                return $node;
            }
        }
        throw new RuntimeException(sprintf('Class %s not found', $classname));
    }


    /**
     * @param  Node\Stmt\ClassMethod $methodNode
     * @param  boolean $forCall
     * @return string
     */
    private function getMethodParameters(Node\Stmt\ClassMethod $methodNode, $forCall = false)
    {
        $parameters = array();

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

        return join(', ', $parameters);
    }

    /**
     * @param Node\Param $parameter
     * @return string
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
            return sprintf(' = \'%s\'', $parameter->default->value);
        }
        if ($parameter->default instanceof Node\Scalar\LNumber) {
            return sprintf(' = %s', $parameter->default->value);
        }
        if ($parameter->default instanceof Node\Scalar\DNumber) {
            return sprintf(' = %s', $parameter->default->value);
        }

        throw new RuntimeException(sprintf('Unsupport default value for parameter %s', $parameter->name));
    }


    /**
     * @param Node\Stmt\ClassMethod $method
     * @param $classname
     * @return string
     */
    private function getDocblock(Node\Stmt\ClassMethod $method, $classname)
    {
        $parser = new DocBlockParser();
        return $parser->parse($method->getDocComment(), $classname, $method->name);
    }

    /**
     * @param Node\Stmt\Class_ $classNode
     * @return Node\Stmt\ClassMethod[]
     */
    private function getPublicMethodNodes(Node\Stmt\Class_ $classNode)
    {
        $methodNodes = array();
        foreach ($classNode->stmts as $node) {
            if ($node instanceof Node\Stmt\ClassMethod && $node->isPublic())
                $methodNodes[] = $node;
        }
        return $methodNodes;
    }

}
