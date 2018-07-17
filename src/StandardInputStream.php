<?php

namespace alexeevdv\agi;

use Exception;
use RuntimeException;

/**
 * Class StandardInputStream
 * @package alexeevdv\agi
 */
class StandardInputStream implements InputStreamInterface
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * StandardInputStream constructor.
     * @param string $filename
     * @param string $mode
     */
    public function __construct($filename = 'php://stdin', $mode = 'r')
    {
        try {
            $this->stream = fopen($filename, $mode);
        } catch (Exception $e) {
            $this->stream = false;
        }
        if ($this->stream === false) {
            throw new RuntimeException('Can`t open input stream - ' . $filename . ':' . $mode);
        }
    }

    /**
     * StandardInputStream destructor
     */
    public function __destruct()
    {
        if ($this->stream !== false) {
            fclose($this->stream);
        }
    }

    /**
     * @inheritdoc
     */
    public function readLine()
    {
        $string = fgets($this->stream, 4096);
        if ($string === false) {
            return null;
        }

        return $string;
    }
}
