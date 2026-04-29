<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class ErrorLog
{
    private static ?self $instance = null;
    private readonly Logger $logger;

    private readonly string $defaultEnvironmentUrl;

    private function __construct()
    {
        $this->defaultEnvironmentUrl = '';

        $todayDate = (new DateTime())->format('Y-m-d');
        $pathsManager = controller::getInstance()->getPathsManager();
        $logFilePath = $pathsManager->getPath('logs') . $todayDate . '.log';
        $this->logger = new Logger('error_log');
        $streamHandler = new StreamHandler($logFilePath, Logger::DEBUG);

        $formatter = new LineFormatter(null, null, true, true);
        $streamHandler->setFormatter($formatter);

        $this->logger->pushHandler($streamHandler);
    }

    /**
     * @deprecated Use Monolog Logger via DI instead.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function logMessage(string $locationName, string $errorText, ?int $level = null): void
    {
        $logLevel = LogLevel::fromErrorLevel($level ?? E_ERROR);

        $logEntry = sprintf(
            "[%s] [%s] %s: %s | IP: %s | Referer: %s | URL: %s",
            new DateTime()->format(DateTimeInterface::ATOM),
            strtoupper($logLevel->value),
            $locationName,
            $errorText,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_REFERER'] ?? 'unknown',
            $this->getUrl()
        );

        $this->logger->log($logLevel->value, $logEntry);
    }

    private function getUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? $this->defaultEnvironmentUrl;
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return sprintf('%s://%s%s', $scheme, $host, $uri);
    }
}
