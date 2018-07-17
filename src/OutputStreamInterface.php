<?php

namespace alexeevdv\agi;

/**
 * Interface OutputStreamInterface
 * @package alexeevdv\agi
 */
interface OutputStreamInterface
{
    /**
     * @param string $string
     * @return int|null
     */
    public function writeLine($string);

    /**
     * @return bool
     */
    public function flush();
}
