<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Web;

use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Web\FetchUrlAction;
use ZxArt\Assistant\Web\ItchIoFilesDto;
use ZxArt\Assistant\Web\ListItchIoFilesAction;
use ZxArt\Assistant\Web\ViewImageAction;
use ZxArt\Releases\ItchIo\ItchIoDownloads;
use ZxArt\Releases\ItchIo\ItchIoDownloadsPage;
use ZxArt\Releases\ItchIo\ItchIoGameUrl;
use ZxArt\Releases\ItchIo\ItchIoUpload;
use ZxArt\Shared\Http\FetchedPageDto;
use ZxArt\Shared\Http\PageFetcher;
use ZxArt\Shared\Images\ImageDto;
use ZxArt\Shared\Images\RemoteImageReader;
use ZxArt\Shared\Images\StoredImageReader;

final class WebActionsTest extends TestCase
{
    public function testFetchUrlAnswersThePage(): void
    {
        $page = new FetchedPageDto('https://example.com/', 200, 'text/html', '<p>Hi</p>', false, 9);
        $fetcher = $this->createMock(PageFetcher::class);
        $fetcher->method('fetch')->with('https://example.com/')->willReturn($page);
        $action = new FetchUrlAction($fetcher);

        self::assertSame('fetch_url', $action->name());
        self::assertSame(['url'], Arguments::schema($action->schema())['required']);
        self::assertSame($page, $action->read(Arguments::parse('{"url": "https://example.com/"}', $action->schema())));
    }

    public function testViewImageReadsAUrl(): void
    {
        $image = new ImageDto('image/png', 'AAAA');
        $remote = $this->createMock(RemoteImageReader::class);
        $remote->method('read')->with('https://example.com/a.png')->willReturn($image);
        $action = new ViewImageAction($remote, $this->createStub(StoredImageReader::class));

        self::assertSame('view_image', $action->name());
        self::assertSame(['url', 'fileId'], array_keys(Arguments::schema($action->schema())['properties']));
        self::assertSame($image, $this->view($action, '{"url": "https://example.com/a.png"}'));
    }

    public function testViewImageReadsAFileOfTheSite(): void
    {
        $image = new ImageDto('image/png', 'AAAA');
        $stored = $this->createMock(StoredImageReader::class);
        $stored->method('read')->with(15)->willReturn($image);
        $action = new ViewImageAction($this->createStub(RemoteImageReader::class), $stored);

        self::assertSame($image, $this->view($action, '{"fileId": 15}'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unclearImages(): iterable
    {
        yield 'neither' => ['{}'];
        yield 'both' => ['{"url": "https://example.com/a.png", "fileId": 15}'];
    }

    #[DataProvider('unclearImages')]
    public function testViewImageTakesEitherAUrlOrAFile(string $json): void
    {
        $action = new ViewImageAction($this->createStub(RemoteImageReader::class), $this->createStub(StoredImageReader::class));
        $this->expectException(ToolArgumentsException::class);

        $this->view($action, $json);
    }

    public function testListItchIoFilesAnswersTheFilesOfTheGameItsAddressNames(): void
    {
        $uploads = [
            new ItchIoUpload(18621174, 'ZX Spectrum English .tap and .z80 files v1.2', false),
            new ItchIoUpload(18633062, 'Windows English v1.2 (emulator embedded)', true),
        ];
        $game = ItchIoGameUrl::tryFrom('https://charlyitv.itch.io/cats-and-dogs');
        self::assertNotNull($game);
        $downloads = $this->createMock(ItchIoDownloads::class);
        $downloads->method('open')->with($game)->willReturn(new ItchIoDownloadsPage($game, $uploads, 'TOKEN', new CookieJar()));
        $action = new ListItchIoFilesAction($downloads);

        self::assertSame('list_itch_io_files', $action->name());
        self::assertSame(['url'], Arguments::schema($action->schema())['required']);
        self::assertEquals(
            new ItchIoFilesDto('https://charlyitv.itch.io/cats-and-dogs', $uploads),
            $action->read(Arguments::parse('{"url": "https://charlyitv.itch.io/cats-and-dogs/devlog/1"}', $action->schema())),
        );
    }

    public function testListItchIoFilesRefusesAnAddressThatIsNotOfAnItchIoGame(): void
    {
        $action = new ListItchIoFilesAction($this->createStub(ItchIoDownloads::class));
        $this->expectException(ToolArgumentsException::class);

        $action->read(Arguments::parse('{"url": "https://itch.io/games/tag-zx-spectrum"}', $action->schema()));
    }

    private function view(ViewImageAction $action, string $json): ImageDto
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
