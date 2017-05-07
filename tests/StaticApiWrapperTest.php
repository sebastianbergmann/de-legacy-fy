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

class StaticApiWrapperTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp()
    {
        $this->root    = vfsStream::setup();
    }

    public function testGeneratesExpectedApiWrapperForClassUsingReflection()
    {
        $actual = vfsStream::url('root') . '/LibraryWrapper.php';

        $wrapper = new StaticApiWrapper(new ReflectionBasedClassParser());

        $wrapper->generate(
            'Library',
            __DIR__ . '/_fixture/Library.php',
            'LibraryWrapper',
            $actual,
            null
        );

        $this->assertFileEquals(__DIR__ . '/_fixture/LibraryWrapper.php', $actual);
    }

    public function testGeneratesExpectedApiWrapperForClassUsingPhpParser()
    {
        $actual = vfsStream::url('root') . '/LibraryWrapper.php';

        $wrapper = new StaticApiWrapper(new PhpParserBasedClassParser());

        $wrapper->generate(
            'Library',
            __DIR__ . '/_fixture/Library.php',
            'LibraryWrapper',
            $actual,
            null
        );

        $this->assertFileEquals(__DIR__ . '/_fixture/LibraryWrapper.php', $actual, '', false, true);
    }
}
