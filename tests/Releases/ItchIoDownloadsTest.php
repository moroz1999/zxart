<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ZxArt\Releases\ItchIo\ItchIoDownloads;
use ZxArt\Releases\ItchIo\ItchIoGameUrl;
use ZxArt\Releases\ItchIo\ItchIoPageParser;
use ZxArt\Releases\ItchIo\ItchIoUnavailableException;
use ZxArt\Releases\ItchIo\ItchIoUpload;

/**
 * Talks to itch.io through a Guzzle handler that answers from a script and
 * records what was asked.
 */
class ItchIoDownloadsTest extends TestCase
{
    private const string GAME = 'https://charlyitv.itch.io/cats-and-dogs';

    /** @var list<array{request: RequestInterface}> */
    private array $requests = [];

    public function testOpensTheGamePageThatListsItsFiles(): void
    {
        $downloads = $this->downloads(new Response(200, [], $this->page('GAMETOKEN', $this->upload(18621174, 'ZX Spectrum English .tap'))));

        $page = $downloads->open($this->game());

        self::assertEquals([new ItchIoUpload(18621174, 'ZX Spectrum English .tap', false)], $page->uploads);
        self::assertSame('GET', $this->requests[0]['request']->getMethod());
        self::assertSame(self::GAME, (string)$this->requests[0]['request']->getUri());
    }

    public function testOpensTheDownloadPageTheDonationPromptLeadsTo(): void
    {
        $donationPrompt = $this->page('GAMETOKEN', '')
            . '<script>init_ViewGame("#v", {"generate_download_url":"https:\/\/charlyitv.itch.io\/cats-and-dogs\/download_url"});</script>';
        $downloads = $this->downloads(
            new Response(200, [], $donationPrompt),
            new Response(200, [], '{"url":"https:\/\/charlyitv.itch.io\/cats-and-dogs\/download\/eyJpZCI6MX0%3D"}'),
            new Response(200, [], $this->page('PAGETOKEN', $this->upload(18621174, 'ZX Spectrum English .tap'))),
        );

        $page = $downloads->open($this->game());

        self::assertEquals([new ItchIoUpload(18621174, 'ZX Spectrum English .tap', false)], $page->uploads);
        self::assertSame('POST', $this->requests[1]['request']->getMethod());
        self::assertSame('https://charlyitv.itch.io/cats-and-dogs/download_url', (string)$this->requests[1]['request']->getUri());
        self::assertSame('csrf_token=GAMETOKEN', (string)$this->requests[1]['request']->getBody());
        self::assertSame('https://charlyitv.itch.io/cats-and-dogs/download/eyJpZCI6MX0%3D', (string)$this->requests[2]['request']->getUri());
    }

    public function testAsksTheAddressOfAFileWithTheTokenOfItsPage(): void
    {
        $downloads = $this->downloads(
            new Response(200, [], $this->page('PAGETOKEN', $this->upload(18621174, 'ZX Spectrum English .tap'))),
            new Response(200, [], '{"url":"https:\/\/storage.example\/upload2\/game\/1\/18621174?X-Amz-Expires=60","external":false}'),
        );

        $fileUrl = $downloads->fileUrl($downloads->open($this->game()), 18621174);

        self::assertSame('https://storage.example/upload2/game/1/18621174?X-Amz-Expires=60', $fileUrl);
        self::assertSame('POST', $this->requests[1]['request']->getMethod());
        self::assertSame(self::GAME . '/file/18621174?source=game_download&bypass_quarantine=true', (string)$this->requests[1]['request']->getUri());
        self::assertSame('csrf_token=PAGETOKEN', (string)$this->requests[1]['request']->getBody());
    }

    public function testAGamePageWithoutFilesOrDonationPromptIsRefused(): void
    {
        $downloads = $this->downloads(new Response(200, [], $this->page('GAMETOKEN', '<p>Coming soon</p>')));

        $this->expectException(ItchIoUnavailableException::class);
        $downloads->open($this->game());
    }

    public function testADownloadPageWithoutFilesIsRefusedNamingWhatCame(): void
    {
        $donationPrompt = $this->page('GAMETOKEN', '')
            . '<script>init_ViewGame("#v", {"generate_download_url":"https:\/\/charlyitv.itch.io\/cats-and-dogs\/download_url"});</script>';
        $downloads = $this->downloads(
            new Response(200, [], $donationPrompt),
            new Response(200, [], '{"url":"https:\/\/charlyitv.itch.io\/cats-and-dogs\/download\/eyJpZCI6MX0%3D"}'),
            new Response(200, [], '<html><head><title>Download link expired - itch.io</title><meta name="csrf_token" value="PAGETOKEN" /></head><body></body></html>'),
        );

        $this->expectException(ItchIoUnavailableException::class);
        $this->expectExceptionMessage('https://charlyitv.itch.io/cats-and-dogs/download/eyJpZCI6MX0%3D lists no files (HTTP 200, "Download link expired - itch.io")');
        $downloads->open($this->game());
    }

    public function testAGamePageThatDoesNotOpenIsRefused(): void
    {
        $downloads = $this->downloads(new Response(404, [], 'Not found'));

        $this->expectException(ItchIoUnavailableException::class);
        $downloads->open($this->game());
    }

    public function testAFileItchIoGivesNoAddressForIsRefused(): void
    {
        $downloads = $this->downloads(
            new Response(200, [], $this->page('PAGETOKEN', $this->upload(18621174, 'ZX Spectrum English .tap'))),
            new Response(200, [], '{"errors":["invalid upload"]}'),
        );
        $page = $downloads->open($this->game());

        $this->expectException(ItchIoUnavailableException::class);
        $downloads->fileUrl($page, 18621174);
    }

    private function game(): ItchIoGameUrl
    {
        $game = ItchIoGameUrl::tryFrom(self::GAME);
        self::assertNotNull($game);

        return $game;
    }

    private function downloads(Response ...$responses): ItchIoDownloads
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->requests));

        return new ItchIoDownloads(new ItchIoPageParser(), new Client(['handler' => $stack]));
    }

    private function page(string $csrfToken, string $body): string
    {
        return '<html><head><meta name="csrf_token" value="' . $csrfToken . '" /></head><body>' . $body . '</body></html>';
    }

    private function upload(int $uploadId, string $name): string
    {
        return '<div class="upload"><a href="javascript:void(0);" data-upload_id="' . $uploadId . '" class="button download_btn">Download</a>'
            . '<div class="info_column"><div class="upload_name"><strong title="' . $name . '" class="name">' . $name . '</strong>'
            . ' <span class="download_platforms"></span></div></div></div>';
    }
}
