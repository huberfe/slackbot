<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 08/12/2017
 * Time: 22:26
 */

namespace Warlof\Seat\Slackbot\Repositories\Slack\Log;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Warlof\Seat\Slackbot\Repositories\Slack\Configuration;

class FileLogger implements LogInterface {

    protected $logger;

    public function __construct() {

        $configuration = Configuration::getInstance();

        $formatter = new LineFormatter('[%datetime%] %channel%.%level_name%: %message%' . PHP_EOL);

        $stream = new RotatingFileHandler(
            $configuration->logfile_location,
            0,
            $configuration->logger_level
        );
        $stream->setFormatter($formatter);

        $this->logger = new Logger('slack');
        $this->logger->pushHandler($stream);
    }

    public function log(string $message, array $context = [])
    {
        $this->logger->addInfo($message, $context);
    }

    public function debug(string $message, array $context = [])
    {
        $this->logger->addDebug($message, $context);
    }

    public function warning(string $message, array $context = []) {
        $this->logger->addWarning($message, $context);
    }

    public function error(string $message, array $context = []) {
        $this->logger->addError($message, $context);
    }

}
