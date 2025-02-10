<?php

namespace App\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;

class Log
{
    private static $logger;

    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::$logger = new Logger('app');
            $streamHandler = new StreamHandler(__DIR__ . '/../../logs/app.log', Logger::DEBUG);
            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                "Y-m-d H:i:s",
                "U.u",
                true,
                true
            );
            $streamHandler->setFormatter($formatter);
            self::$logger->pushHandler($streamHandler);
        }

        return self::$logger;
    }
}