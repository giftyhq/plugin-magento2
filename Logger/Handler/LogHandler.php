<?php

namespace Gifty\Magento\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Log handler for Gifty module
 *
 * Handles the writing of debug logs to a dedicated file for the Gifty module.
 * Extends Magento's base handler to provide consistent logging behavior.
 *
 */
class LogHandler extends BaseHandler
{
    /**
     * Logging level for this handler
     *
     * Set to DEBUG level to capture detailed information about gift card operations.
     * This allows for comprehensive debugging while maintaining Magento's logging standards.
     *
     * @var int
     * @see MonologLogger for available log levels
     */
    protected $loggerType = MonologLogger::DEBUG;

    /**
     * Path to the log file
     *
     * Logs are written to a dedicated file in the Magento var/log directory
     * to keep Gifty-related logs separate from other system logs.
     *
     * @var string
     */
    protected $fileName = '/var/log/gifty/debug.log';
}
