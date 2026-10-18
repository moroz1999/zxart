<?php

declare(strict_types=1);

namespace ZxArt\Tests\Pictures;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use zxPictureElement;
use ZxArt\Pictures\Dto\PictureDownloadDto;
use ZxArt\Pictures\PictureDownloadKind;
use ZxArt\Pictures\Services\PictureDownloadsBuilder;

/**
 * A picture's materials are downloadable next to its renders: the recorded
 * drawing stages and the executable file. Each row names its kind, so the
 * interface labels it, and carries the file's own name where it has one.
 */
#[AllowMockObjectsWithoutExpectations]
class PictureDownloadsBuilderTest extends TestCase
{
    private const string BASE_URL = 'https://zxart.ee/';

    public function testStagesAreOfferedForDownload(): void
    {
        $picture = $this->picture();
        $picture->method('getSequenceFileId')->willReturn('83698_sequence');
        $picture->method('getSequenceFileName')->willReturn('capri gif.gif');

        $download = $this->findKind($this->build($picture), PictureDownloadKind::Stages);

        $this->assertNotNull($download);
        $this->assertSame('gif', $download->ext);
        $this->assertSame('capri gif.gif', $download->fileName);
        $this->assertSame(self::BASE_URL . 'file/id:83698_sequence/filename:capri%20gif.gif', $download->url);
    }

    public function testExecutableIsNamedByItsKind(): void
    {
        $picture = $this->picture(['exe' => 'Capri.zip']);
        $picture->method('getExeFileId')->willReturn('83698_exe');
        $picture->method('getFileExtension')->willReturn('.zip');

        $download = $this->findKind($this->build($picture), PictureDownloadKind::Executable);

        $this->assertNotNull($download);
        $this->assertSame('zip', $download->ext);
        $this->assertSame('Capri.zip', $download->fileName);
    }

    public function testPictureWithoutFilesOffersNothing(): void
    {
        $this->assertSame([], $this->build($this->picture()));
    }

    /**
     * @param array<string, string> $fileNames file name by file type; a type without one has no file
     */
    private function picture(array $fileNames = []): zxPictureElement&MockObject
    {
        $picture = $this->createMock(zxPictureElement::class);
        $picture->method('getId')->willReturn(83698);
        $picture->method('getFileName')->willReturnCallback(
            static fn(string $type): string => $fileNames[$type] ?? '',
        );
        return $picture;
    }

    /**
     * @return PictureDownloadDto[]
     */
    private function build(zxPictureElement $picture): array
    {
        return (new PictureDownloadsBuilder())->build($picture, self::BASE_URL);
    }

    /**
     * @param PictureDownloadDto[] $downloads
     */
    private function findKind(array $downloads, PictureDownloadKind $kind): ?PictureDownloadDto
    {
        foreach ($downloads as $download) {
            if ($download->kind === $kind) {
                return $download;
            }
        }
        return null;
    }
}
