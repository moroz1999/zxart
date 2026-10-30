<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared\Http;

require_once __DIR__ . '/../../Doubles/Http/LocalFileServer.php';
require_once __DIR__ . '/../../Doubles/Http/LoopbackUrlGuard.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Http\FetchedPageDto;
use ZxArt\Shared\Http\GuardedHttpClient;
use ZxArt\Shared\Http\PageFetcher;
use ZxArt\Shared\Http\HttpRequestRefusedException;
use ZxArt\Tests\Doubles\Http\LocalFileServer;
use ZxArt\Tests\Doubles\Http\LoopbackUrlGuard;

/**
 * Fetches pages from PHP's built-in server, through a guard that lets only its
 * host through.
 */
final class PageFetcherTest extends TestCase
{
    private LocalFileServer $server;

    protected function setUp(): void
    {
        $this->server = new LocalFileServer();
    }

    protected function tearDown(): void
    {
        $this->server->stop();
    }

    public function testAPageComesAsItCameWithItsStatusAndType(): void
    {
        self::assertEquals(new FetchedPageDto(
            finalUrl: $this->server->url('/page.html'),
            status: 200,
            contentType: 'text/html',
            body: '<!doctype html><html><head><title>Page</title></head><body><p>Not a picture</p></body></html>',
            truncated: false,
            size: 93,
        ), $this->fetcher()->fetch($this->server->url('/page.html')));
    }

    public function testTheFinalUrlIsWhereTheRedirectsLed(): void
    {
        $page = $this->fetcher()->fetch($this->server->url('/redirect-page'));

        self::assertSame($this->server->url('/page.html'), $page->finalUrl);
        self::assertStringStartsWith('<!doctype html>', (string)$page->body);
    }

    public function testTheDeclaredCharsetIsConvertedToUtf8(): void
    {
        self::assertSame('Привет, Спектрум', $this->fetcher()->fetch($this->server->url('/page-1251.txt'))->body);
    }

    public function testAnHtmlPageMayDeclareItsCharsetInAMetaTag(): void
    {
        self::assertStringContainsString('<body>Игра</body>', (string)$this->fetcher()->fetch($this->server->url('/meta-1251.html'))->body);
    }

    public function testABodyOverTheLimitIsCutWithoutBreakingACharacter(): void
    {
        $page = $this->fetcher()->fetch($this->server->url('/long.txt'), 1001);

        self::assertTrue($page->truncated);
        self::assertSame(str_repeat('ж', 500), $page->body);
    }

    public function testAnErrorPageIsAnsweredWithItsStatus(): void
    {
        $page = $this->fetcher()->fetch($this->server->url('/missing.html'));

        self::assertSame(404, $page->status);
        self::assertSame('<p>No such page</p>', $page->body);
    }

    public function testABinaryResponseAnswersItsTypeAndSizeButNoBody(): void
    {
        $page = $this->fetcher()->fetch($this->server->url('/image.png'));

        self::assertSame('image/png', $page->contentType);
        self::assertSame(68, $page->size);
        self::assertNull($page->body);
    }

    public function testTheBodyOfARedirectIsNotTakenForThePage(): void
    {
        self::assertSame(93, $this->fetcher()->fetch($this->server->url('/redirect-page'))->size);
    }

    public function testARefusedAddressIsNotFetched(): void
    {
        $this->expectException(HttpRequestRefusedException::class);

        $this->fetcher()->fetch($this->server->url('/page.html', 'refused.test'));
    }

    private function fetcher(): PageFetcher
    {
        return new PageFetcher(new GuardedHttpClient(new LoopbackUrlGuard()));
    }
}
