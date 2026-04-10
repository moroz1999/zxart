<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use Monolog\Logger;
use Throwable;

abstract class LoggedControllerApplication extends controllerApplication
{
    public function __construct(
        controller $controller,
        protected readonly Logger $logger,
    ) {
        parent::__construct($controller);
    }

    protected function logThrowable(string $location, Throwable $throwable): void
    {
        $this->logger->error(
            $location . ': ' . $throwable->getMessage() . "\n"
            . $throwable->getTraceAsString() . "\n"
            . 'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | '
            . 'Referer: ' . ($_SERVER['HTTP_REFERER'] ?? 'unknown') . ' | '
            . 'URL: ' . $this->getRequestUrl()
        );
    }
}
