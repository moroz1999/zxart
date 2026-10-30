<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\Http;

require_once __DIR__ . '/../../Doubles/Http/LocalFileServer.php';
require_once __DIR__ . '/../../Doubles/Http/LoopbackUrlGuard.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Http\DownloadRefusedException;
use ZxArt\Shared\Http\GuardedDownloader;
use ZxArt\Shared\Http\GuardedHttpClient;
use ZxArt\Tests\Doubles\Http\LocalFileServer;
use ZxArt\Tests\Doubles\Http\LoopbackUrlGuard;

/**
 * Downloads from PHP's built-in server, through a guard that lets only its
 * host through.
 */
class GuardedDownloaderTest extends TestCase
{
    private LocalFileServer $server;
    private string $destination = '';

    protected function setUp(): void
    {
        $this->server = new LocalFileServer();
        $this->destination = (string)tempnam(sys_get_temp_dir(), 'download');
    }

    protected function tearDown(): void
    {
        $this->server->stop();
        if (is_file($this->destination)) {
            unlink($this->destination);
        }
    }

    public function testDownloadsToTheDestinationAndTellsWhatCame(): void
    {
        $file = $this->createDownloader()->download($this->url('/image.png'), $this->destination, 1000);

        self::assertSame($this->url('/image.png'), $file->finalUrl);
        self::assertSame('image/png', $file->contentType);
        self::assertSame(filesize($this->destination), $file->size);
    }

    public function testTheRequestGoesToThePinnedAddressUnderTheHostsName(): void
    {
        $this->createDownloader()->download($this->url('/host'), $this->destination, 1000);

        self::assertSame(parse_url($this->url('/host'), PHP_URL_HOST) . ':' . parse_url($this->url('/host'), PHP_URL_PORT), file_get_contents($this->destination));
    }

    public function testFollowsARedirect(): void
    {
        $file = $this->createDownloader()->download($this->url('/redirect'), $this->destination, 2000);

        self::assertSame($this->url('/file.bin'), $file->finalUrl);
        self::assertSame(1000, $file->size);
    }

    public function testARedirectToARefusedHostIsRefused(): void
    {
        $this->expectException(DownloadRefusedException::class);
        $this->createDownloader()->download($this->url('/redirect-away'), $this->destination, 2000);
    }

    public function testEndlessRedirectsAreRefused(): void
    {
        $this->expectException(DownloadRefusedException::class);
        $this->createDownloader()->download($this->url('/loop'), $this->destination, 2000);
    }

    public function testAFileOverTheLimitIsRefusedAndNotKept(): void
    {
        try {
            $this->createDownloader()->download($this->url('/big.bin'), $this->destination, 4000);
            self::fail('DownloadRefusedException was not thrown');
        } catch (DownloadRefusedException) {
            self::assertFileDoesNotExist($this->destination);
        }
    }

    public function testAnErrorStatusIsRefused(): void
    {
        $this->expectException(DownloadRefusedException::class);
        $this->createDownloader()->download($this->url('/missing'), $this->destination, 2000);
    }

    public function testARefusedUrlIsNotRequested(): void
    {
        $this->expectException(DownloadRefusedException::class);
        $this->createDownloader()->download($this->server->url('/file.bin', 'refused.test'), $this->destination, 2000);
    }

    private function url(string $path): string
    {
        return $this->server->url($path);
    }

    private function createDownloader(): GuardedDownloader
    {
        return new GuardedDownloader(new GuardedHttpClient(new LoopbackUrlGuard()));
    }
}
