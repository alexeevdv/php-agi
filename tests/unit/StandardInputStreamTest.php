<?php

namespace tests\unit;

use alexeevdv\agi\InputStreamInterface;
use alexeevdv\agi\StandardInputStream;
use RuntimeException;

/**
 * Class StandardInputStreamTest
 * @package tests\unit
 */
class StandardInputStreamTest extends \Codeception\Test\Unit
{
    /**
     * @test
     */
    public function failedInstantiation()
    {
        $this->expectException(RuntimeException::class);
        new StandardInputStream('php://c++');
    }

    /**
     * @test
     */
    public function successfulInstantiation()
    {
        $stream = new StandardInputStream();
        $this->assertInstanceOf(InputStreamInterface::class, $stream);
    }

    /**
     * @test
     */
    public function readLine()
    {
        $inputFile = __DIR__ . '/../_data/StandardInputStream/readLine.txt';
        $stream = new StandardInputStream($inputFile);
        $this->assertEquals("First line\n", $stream->readLine());
        $this->assertEquals("Second line\n", $stream->readLine());
        $this->assertEquals("\n", $stream->readLine());
        $this->assertEquals("Fourth line", $stream->readLine());
        $this->assertEquals(null, $stream->readLine());

    }
}
