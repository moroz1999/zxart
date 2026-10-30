<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\Images;

require_once __DIR__ . '/../../Doubles/Http/LocalFileServer.php';
require_once __DIR__ . '/../../Doubles/Http/LoopbackUrlGuard.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Http\GuardedHttpClient;
use ZxArt\Shared\Images\ImageDto;
use ZxArt\Shared\Images\ImageRefusedException;
use ZxArt\Shared\Images\RemoteImageReader;
use ZxArt\Tests\Doubles\Http\LocalFileServer;
use ZxArt\Tests\Doubles\Http\LoopbackUrlGuard;

final class RemoteImageReaderTest extends TestCase
{
    private const string PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    private LocalFileServer $server;

    protected function setUp(): void
    {
        $this->server = new LocalFileServer();
    }

    protected function tearDown(): void
    {
        $this->server->stop();
    }

    public function testAnImageComesWithItsTypeInBase64(): void
    {
        self::assertEquals(new ImageDto('image/png', self::PNG), $this->reader()->read($this->server->url('/image.png')));
    }

    public function testAnImageIsFoundBehindARedirect(): void
    {
        self::assertSame('image/png', $this->reader()->read($this->server->url('/redirect-image'))->mediaType);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function refusals(): iterable
    {
        yield 'not an image' => ['/page.html', 'files.test'];
        yield 'an error status' => ['/missing.html', 'files.test'];
        yield 'a refused address' => ['/image.png', 'refused.test'];
    }

    #[DataProvider('refusals')]
    public function testWhatIsNotAnImageIsRefused(string $path, string $host): void
    {
        $this->expectException(ImageRefusedException::class);

        $this->reader()->read($this->server->url($path, $host));
    }

    public function testAnImageOverTheLimitIsRefused(): void
    {
        $this->expectException(ImageRefusedException::class);

        $this->reader()->read($this->server->url('/image.png'), 50);
    }

    private function reader(): RemoteImageReader
    {
        return new RemoteImageReader(new GuardedHttpClient(new LoopbackUrlGuard()));
    }
}
