<?php

namespace tests\unit;

use alexeevdv\agi\OutputStreamInterface;
use RuntimeException;
use alexeevdv\agi\StandardOutputStream;

/**
 * Class StandardOutputStreamTest
 * @package tests\unit
 */
class StandardOutputStreamTest extends \Codeception\Test\Unit
{
    /**
     * @test
     */
    public function failedInstantiation()
    {
        $this->expectException(RuntimeException::class);
        new StandardOutputStream('php://c++');
    }

    /**
     * @test
     */
    public function successfulInstantiation()
    {
        $stream = new StandardOutputStream();
        $this->assertInstanceOf(OutputStreamInterface::class, $stream);
    }

    /**
     * @test
     */
    public function writeLine()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        $stream = new StandardOutputStream($tempFile);
        $stream->writeLine('First line');
        $stream->writeLine("Second line\n");
        $stream->writeLine('Fourth line');
        $stream->flush();

        $expectedText = "First line\n"
            . "Second line\n"
            . "\n"
            . "Fourth line\n";

        $this->assertStringEqualsFile($tempFile, $expectedText);
    }

    /**
     * @test
     */
    public function writeLineReturnValue()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        $stream = new StandardOutputStream($tempFile);
        $this->assertEquals(6, $stream->writeLine('12345'));
    }

    /**
     * @test
     */
    public function writeLineFailed()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        $stream = new StandardOutputStream($tempFile, 'r');
        $this->assertNull($stream->writeLine('12345'));
    }
}
