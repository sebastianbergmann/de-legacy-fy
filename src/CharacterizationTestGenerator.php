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

/**
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link      http://github.com/sebastianbergmann/de-legacy-fy/tree
 * @since     Class available since Release 1.0.0
 */
class CharacterizationTestGenerator
{
    /**
     * @var string
     */
    private static $classTemplate = '<?php
class %s extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function provider()
    {
        return array(
%s
        );
    }

    /**
     * @param  string $value
     * @return mixed
     */
    private function decode($value)
    {
        return unserialize(base64_decode($value));
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

        for ($i = 0; $i < count($data); $i++) {
            $last = $i == count($data) - 1;

            $buffer .= sprintf(
                "            array(%s)%s",
                join(
                    ', ',
                    array_map(
                        function ($parameter) {
                            return '$this->decode(\'' . $parameter . '\')';
                        },
                        $data[$i]
                    )
                ),
                !$last ? ",\n" : ''
            );
        }

        file_put_contents(
            $testFile,
            sprintf(
                self::$classTemplate,
                $testClass,
                $buffer
            )
        );
    }
}
