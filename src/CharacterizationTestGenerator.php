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

class CharacterizationTestGenerator
{
    /**
     * @var string
     */
    private static $classTemplate = '<?php
use PHPUnit\Framework\TestCase;

class %s extends TestCase
{
    /**
     * @return array
     */
    public function provider()
    {
        return [
%s
        ];
    }

    /**
     * @param string $serializedValue
     *
     * @return mixed
     */
    private function decode($serializedValue)
    {
        return unserialize(base64_decode($serializedValue));
    }
}
';

    /**
     * @param string $traceFile
     * @param string $unit
     * @param string $testClass
     * @param string $testFile
     */
    public function generate($traceFile, $unit, $testClass, $testFile)
    {
        $parser = new XdebugTraceParser;
        $data   = $parser->parse($traceFile, $unit);
        $buffer = '';

        for ($i = 0; $i < \count($data); $i++) {
            $last = $i == \count($data) - 1;

            $buffer .= \sprintf(
                '            [%s]%s',
                \implode(
                    ', ',
                    \array_map(
                        function ($parameter) {
                            return '$this->decode(\'' . $parameter . '\')';
                        },
                        $data[$i]
                    )
                ),
                !$last ? ",\n" : ''
            );
        }

        \file_put_contents(
            $testFile,
            \sprintf(
                self::$classTemplate,
                $testClass,
                $buffer
            )
        );
    }
}
