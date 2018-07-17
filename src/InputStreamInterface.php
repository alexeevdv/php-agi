<?php

namespace alexeevdv\agi;

/**
 * Interface InputStreamInterface
 * @package alexeevdv\agi
 */
interface InputStreamInterface
{
    /**
     * @return string|null
     */
    public function readLine();
}
