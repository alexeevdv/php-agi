<?php

namespace alexeevdv\agi;

use Exception;
use RuntimeException;

class StandardOutputStream implements OutputStreamInterface
{
    /**
     * @var resource
     */
    private $stream;

    public function __construct($filename = 'php://stdout', $mode = 'w')
    {
        try {
            $this->stream = fopen($filename, $mode);
        } catch(Exception $e) {
            $this->stream = false;
        }
        if ($this->stream === false) {
            throw new RuntimeException('Can`t open output stream - ' . $filename . ':' . $mode);
        }
    }

    public function __destruct()
    {
        if ($this->stream !== false) {
            fclose($this->stream);
        }
    }

    public function writeLine(string $string): ?int
    {
        $written = fwrite($this->stream, $string . "\n");
        if (!$written) {
            return null;
        }
        return $written;
    }

    public function flush(): bool
    {
        return fflush($this->stream);
    }
}
