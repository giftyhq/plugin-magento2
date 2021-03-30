<?php

namespace Gifty\Magento\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

class LogHandler extends BaseHandler
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = MonologLogger::DEBUG;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/gifty/debug.log';
}
