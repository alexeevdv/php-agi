<?php

namespace tests\unit;

use alexeevdv\agi\AGI;
use alexeevdv\agi\InputStreamInterface;
use alexeevdv\agi\OutputStreamInterface;
use Codeception\Stub;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class AGITest
 * @package tests\unit
 */
class AGITest extends \Codeception\Test\Unit
{
    /**
     * @throws Exception
     * @test
     */
    public function successfulInstantiationWithoutInput()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = new AGI($input, $output);
        $this->assertInstanceOf(AGI::class, $agi);
    }

    /**
     * @test
     */
    public function successfulInstantiationWithInput()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class, [
            'readLine' => Stub::consecutive(
                "name1: value1\n",
                "name2: value2\n",
                "name3: value3\n",
                "\n"
            )
        ]);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = new AGI($input, $output);
        $this->assertInstanceOf(AGI::class, $agi);
        $this->assertEquals('value2', $agi->getRequestVariable('name2'));
        $this->assertNull($agi->getRequestVariable('name4'));
    }

    /**
     * @test
     */
    public function getVariable()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = Stub::constructEmptyExcept(AGI::class, 'getVariable', [$input, $output], [
            'evaluate' => function ($command) {
                $this->assertEquals('GET VARIABLE abc', $command);
                return ['data' => '321'];
            },
        ], $this);
        $value = $agi->getVariable('abc');
        $this->assertArrayHasKey('data', $value);
        $this->assertEquals($value['data'], '321');
    }

    /**
     * @test
     */
    public function getVariableWithGetValue()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = Stub::constructEmptyExcept(AGI::class, 'getVariable', [$input, $output], [
            'evaluate' => function ($command) {
                $this->assertEquals('GET VARIABLE abc', $command);
                return ['data' => '321'];
            },
        ], $this);
        $value = $agi->getVariable('abc', true);
        $this->assertEquals('321', $value);
    }

    /**
     * @test
     */
    public function hangup()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = Stub::constructEmptyExcept(AGI::class, 'hangup', [$input, $output], [
            'evaluate' => function ($command) {
                $this->assertEquals('HANGUP abc', $command);
                return ['result' => 1];
            },
        ], $this);
        $value = $agi->hangup('abc');
        $this->assertArrayHasKey('result', $value);
        $this->assertEquals($value['result'], 1);
    }

    /**
     * @test
     */
    public function execWithStringOptions()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = Stub::constructEmptyExcept(AGI::class, 'exec', [$input, $output], [
            'evaluate' => function ($command) {
                $this->assertEquals('EXEC app opt', $command);
                return ['result' => 1];
            },
            'getLogger' => Stub::makeEmpty(LoggerInterface::class),
        ], $this);
        $value = $agi->exec('app', 'opt');
        $this->assertArrayHasKey('result', $value);
        $this->assertEquals($value['result'], 1);
    }

    /**
     * @test
     */
    public function execWithArrayOptions()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = Stub::constructEmptyExcept(AGI::class, 'exec', [$input, $output], [
            'evaluate' => function ($command) {
                $this->assertEquals('EXEC app opt|opt1|opt2', $command);
                return ['result' => 1];
            },
            'getLogger' => Stub::makeEmpty(LoggerInterface::class),
        ], $this);
        $value = $agi->exec('app', ['opt', 'opt1', 'opt2']);
        $this->assertArrayHasKey('result', $value);
        $this->assertEquals($value['result'], 1);
    }

    /**
     * @test
     */
    public function verbose()
    {
        $input = Stub::makeEmpty(InputStreamInterface::class);
        $output = Stub::makeEmpty(OutputStreamInterface::class);
        $agi = Stub::constructEmptyExcept(AGI::class, 'verbose', [$input, $output], [
            'evaluate' => function ($command) {
                $this->assertEquals('VERBOSE "message" 3', $command);
                return ['result' => 1];
            },
            'getLogger' => Stub::makeEmpty(LoggerInterface::class),
        ], $this);
        $value = $agi->verbose("message", 3);
        $this->assertArrayHasKey('result', $value);
        $this->assertEquals($value['result'], 1);
    }
}
