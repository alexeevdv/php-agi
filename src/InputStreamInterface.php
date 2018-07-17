<?php

namespace alexeevdv\agi;

interface InputStreamInterface
{
    public function readLine(): ?string;
}
