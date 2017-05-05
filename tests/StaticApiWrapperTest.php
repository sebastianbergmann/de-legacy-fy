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

    /**
     * @var StaticApiWrapper
     */
    private $wrapper;

    protected function setUp()
    {
        $this->root    = vfsStream::setup();
        $this->wrapper = new StaticApiWrapper;
    }

    public function testGeneratesApiWrapperForClass()
    {
        $actual = vfsStream::url('root') . '/LibraryWrapper.php';

        $this->wrapper->generate(
            'Library',
            __DIR__ . '/_fixture/Library.php',
            'LibraryWrapper',
            $actual,
            null
        );

        $this->assertFileEquals(__DIR__ . '/_fixture/LibraryWrapper.php', $actual);
    }
}
