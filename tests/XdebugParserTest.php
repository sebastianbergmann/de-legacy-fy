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

use PHPUnit\Framework\TestCase;

class XdebugParserTest extends TestCase
{
    /**
     * @var XdebugTraceParser
     */
    private $parser;

    protected function setUp()
    {
        $this->parser = new XdebugTraceParser;
    }

    public function testParserWorksCorrectly()
    {
        $this->assertEquals(
            array(
                array(
                    'aTozOw==',
                    'aToxOw==',
                    'aToyOw=='
                )
            ),
            $this->parser->parse(
                __DIR__ . '/_fixture/xdebug-2.3.xt',
                'add'
            )
        );
    }

    /**
     * @expectedException \SebastianBergmann\DeLegacyFy\RuntimeException
     */
    public function testExceptionIsRaisedForUnsupportedFileFormat()
    {
        $this->parser->parse(
            __DIR__ . '/_fixture/xdebug-2.2.xt',
            'add'
        );
    }
}
