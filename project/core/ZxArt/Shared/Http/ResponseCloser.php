<?php

declare(strict_types=1);

namespace ZxArt\Shared\Http;

/**
 * Lets a request go on working after its response has reached the client: the
 * session is released so the client's next requests are not blocked on it, and
 * the connection is closed.
 */
final readonly class ResponseCloser
{
    /**
     * Before the response is rendered: the client must not wait for more.
     */
    public function prepare(): void
    {
        if (!headers_sent()) {
            header('Connection: close');
        }
    }

    /**
     * After the response is rendered.
     */
    public function close(int $timeLimitSeconds): void
    {
        ignore_user_abort(true);
        set_time_limit($timeLimitSeconds);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }
}
