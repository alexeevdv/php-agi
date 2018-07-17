<?php

namespace alexeevdv\agi;

use Psr\Log\AbstractLogger;

/**
 * Class DummyLogger
 * @package alexeevdv\agi
 */
class DummyLogger extends AbstractLogger
{
    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = array())
    {
    }
}
