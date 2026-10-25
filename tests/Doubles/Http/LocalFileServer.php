<?php

declare(strict_types=1);

namespace ZxArt\Tests\Doubles\Http;

use RuntimeException;

/**
 * PHP's built-in server on a loopback port, serving `router.php`. Its URLs
 * name the host `files.test`, which {@see LoopbackUrlGuard} pins to it.
 */
final class LocalFileServer
{
    /** @var resource */
    private $process;
    private int $port;

    public function __construct()
    {
        $this->port = random_int(20000, 40000);
        $process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $this->port, __DIR__ . '/router.php'],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
        );
        if ($process === false) {
            throw new RuntimeException('The file server did not start');
        }
        $this->process = $process;
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $socket = @fsockopen('127.0.0.1', $this->port);
            if ($socket !== false) {
                fclose($socket);

                return;
            }
            usleep(100000);
        }
        throw new RuntimeException('The file server does not answer');
    }

    public function url(string $path, string $host = LoopbackUrlGuard::HOST): string
    {
        return 'http://' . $host . ':' . $this->port . $path;
    }

    public function stop(): void
    {
        proc_terminate($this->process);
        proc_close($this->process);
    }
}
