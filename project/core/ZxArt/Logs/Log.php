<?php
declare(strict_types=1);


namespace ZxArt\Logs;

use DateTime;
use LogLevel;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final readonly class Log
{
    public function __construct(
        private Logger $logger,
        string         $logPath,
    )
    {
        $todayDate = (new DateTime())->format('Y-m-d');
        $logFilePath = $logPath . $todayDate . '.log';
        $streamHandler = new StreamHandler($logFilePath, Logger::DEBUG);

        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);

        $this->logger->pushHandler($streamHandler);
    }

    /**
     * @throws JsonException
     */
    public function logMessage(string $text, ?int $level = null, ?int $id = null): void
    {
        $logLevel = LogLevel::fromErrorLevel($level ?? E_ERROR);

        $logEntry = sprintf(
            "[%s] : %s",
            (string)($id ?? ''),
            $text,
        );

        $this->logger->log($logLevel->value, $logEntry);
    }
}