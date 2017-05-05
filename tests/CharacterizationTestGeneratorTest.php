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
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class CharacterizationTestGeneratorTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var CharacterizationTestGenerator
     */
    private $generator;

    protected function setUp()
    {
        $this->root      = vfsStream::setup();
        $this->generator = new CharacterizationTestGenerator;
    }

    public function testGeneratesDataProvider()
    {
        $actual = vfsStream::url('root') . '/CharacterizationTest.php';

        $this->generator->generate(
            __DIR__ . '/_fixture/xdebug-2.3.xt',
            'add',
            'CharacterizationTest',
            $actual
        );

        $this->assertFileEquals(__DIR__ . '/_fixture/CharacterizationTest.txt', $actual);
    }
}
