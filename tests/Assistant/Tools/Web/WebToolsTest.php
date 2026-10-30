<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Web;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\ToolArgumentsException;
use ZxArt\Assistant\Tools\Web\FetchUrlRequestDto;
use ZxArt\Assistant\Tools\Web\FetchUrlTool;
use ZxArt\Assistant\Tools\Web\ViewImageRequestDto;
use ZxArt\Assistant\Tools\Web\ViewImageTool;
use ZxArt\Shared\Http\FetchedPageDto;
use ZxArt\Shared\Http\PageFetcher;
use ZxArt\Shared\Images\ImageDto;
use ZxArt\Shared\Images\RemoteImageReader;
use ZxArt\Shared\Images\StoredImageReader;

final class WebToolsTest extends TestCase
{
    public function testFetchUrlAnswersThePage(): void
    {
        $page = new FetchedPageDto('https://example.com/', 200, 'text/html', '<p>Hi</p>', false, 9);
        $fetcher = $this->createMock(PageFetcher::class);
        $fetcher->method('fetch')->with('https://example.com/')->willReturn($page);
        $tool = new FetchUrlTool($fetcher);

        self::assertSame('fetch_url', $tool->name());
        self::assertSame(FetchUrlRequestDto::class, $tool->requestClass());
        self::assertSame($page, $tool->execute(new FetchUrlRequestDto('https://example.com/')));
    }

    public function testViewImageReadsAUrl(): void
    {
        $image = new ImageDto('image/png', 'AAAA');
        $remote = $this->createMock(RemoteImageReader::class);
        $remote->method('read')->with('https://example.com/a.png')->willReturn($image);
        $tool = new ViewImageTool($remote, $this->createStub(StoredImageReader::class));

        self::assertSame('view_image', $tool->name());
        self::assertSame($image, $tool->execute(new ViewImageRequestDto(url: 'https://example.com/a.png')));
    }

    public function testViewImageReadsAFileOfTheSite(): void
    {
        $image = new ImageDto('image/png', 'AAAA');
        $stored = $this->createMock(StoredImageReader::class);
        $stored->method('read')->with(15)->willReturn($image);
        $tool = new ViewImageTool($this->createStub(RemoteImageReader::class), $stored);

        self::assertSame($image, $tool->execute(new ViewImageRequestDto(fileId: 15)));
    }

    /**
     * @return iterable<string, array{ViewImageRequestDto}>
     */
    public static function unclearImages(): iterable
    {
        yield 'neither' => [new ViewImageRequestDto()];
        yield 'both' => [new ViewImageRequestDto(url: 'https://example.com/a.png', fileId: 15)];
    }

    #[DataProvider('unclearImages')]
    public function testViewImageTakesEitherAUrlOrAFile(ViewImageRequestDto $request): void
    {
        $tool = new ViewImageTool($this->createStub(RemoteImageReader::class), $this->createStub(StoredImageReader::class));
        $this->expectException(ToolArgumentsException::class);

        $tool->execute($request);
    }
}
