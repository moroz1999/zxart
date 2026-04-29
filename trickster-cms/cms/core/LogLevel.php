<?php

enum LogLevel: string
{
    case EMERGENCY = 'emergency';
    case ALERT = 'alert';
    case CRITICAL = 'critical';
    case ERROR = 'error';
    case WARNING = 'warning';
    case NOTICE = 'notice';
    case INFO = 'info';
    case DEBUG = 'debug';

    public static function fromErrorLevel(int $errorLevel): self
    {
        return match ($errorLevel) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => self::ERROR,
            E_WARNING, E_USER_WARNING, E_CORE_WARNING, E_COMPILE_WARNING => self::WARNING,
            E_PARSE => self::CRITICAL,
            E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED => self::NOTICE,
            default => self::DEBUG,
        };
    }
}