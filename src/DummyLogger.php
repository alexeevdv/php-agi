<?php

namespace alexeevdv\agi;

use Psr\Log\AbstractLogger;

class DummyLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
    }
}
