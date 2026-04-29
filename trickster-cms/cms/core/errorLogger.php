<?php

class errorLogger
{
    protected function logError($message, $level = null): void
    {
        ErrorLog::getInstance()->logMessage($this->getErrorLogLocation(), $message, $level);
    }

    protected function getErrorLogLocation(): string
    {
        return get_class($this);
    }
}