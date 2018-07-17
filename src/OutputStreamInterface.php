<?php

namespace alexeevdv\agi;

interface OutputStreamInterface
{
    public function writeLine(string $string): ?int;

    public function flush(): bool;
}
