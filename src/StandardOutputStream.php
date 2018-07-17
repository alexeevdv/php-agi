<?php

namespace alexeevdv\agi;

use Exception;
use RuntimeException;

/**
 * Class StandardOutputStream
 * @package alexeevdv\agi
 */
class StandardOutputStream implements OutputStreamInterface
{
    /**
     * @var resource
     */
    private $stream;

    /**
     * StandardOutputStream constructor.
     * @param string $filename
     * @param string $mode
     */
    public function __construct($filename = 'php://stdout', $mode = 'w')
    {
        try {
            $this->stream = fopen($filename, $mode);
        } catch (Exception $e) {
            $this->stream = false;
        }
        if ($this->stream === false) {
            throw new RuntimeException('Can`t open output stream - ' . $filename . ':' . $mode);
        }
    }

    /**
     * StandardOutputStream destructor
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
    public function writeLine($string)
    {
        $written = fwrite($this->stream, $string . "\n");
        if (!$written) {
            return null;
        }
        return $written;
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        return fflush($this->stream);
    }
}
