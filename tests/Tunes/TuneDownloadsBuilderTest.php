<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tunes;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use zxMusicElement;
use ZxArt\Tunes\Dto\TuneDownloadDto;
use ZxArt\Tunes\Services\TuneDownloadsBuilder;
use ZxArt\Tunes\TuneDownloadKind;

/**
 * A tune offers the file it is played from, its tracker source, its executable
 * and the recorded audio. Each row names its kind, so the interface labels it,
 * and carries the file's own name where it has one.
 */
#[AllowMockObjectsWithoutExpectations]
class TuneDownloadsBuilderTest extends TestCase
{
    private const string BASE_URL = 'https://zxart.ee/';

    public function testExecutableIsOfferedApartFromTheTrackerFile(): void
    {
        $tune = $this->tune(['tracker' => 'Song.vt2', 'exe' => 'Song.tap']);
        $tune->method('getExeFileId')->willReturn('45113_tracker');

        $downloads = $this->build($tune, trackerFileUrl: '/file/id:45113_t/filename:Song.vt2');

        $tracker = $this->findKind($downloads, TuneDownloadKind::Tracker);
        $executable = $this->findKind($downloads, TuneDownloadKind::Executable);
        $this->assertNotNull($tracker);
        $this->assertSame('Song.vt2', $tracker->fileName);
        $this->assertNotNull($executable);
        $this->assertSame('tap', $executable->ext);
        $this->assertSame('Song.tap', $executable->fileName);
        $this->assertSame(self::BASE_URL . 'file/id:45113_tracker/filename:Song.tap', $executable->url);
    }

    public function testTuneWithoutFilesOffersNothing(): void
    {
        $this->assertSame([], $this->build($this->tune([])));
    }

    public function testRecordedAudioIsOffered(): void
    {
        $downloads = $this->build($this->tune([]), mp3Url: '/music/1.ogg');

        $audio = $this->findKind($downloads, TuneDownloadKind::Audio);
        $this->assertNotNull($audio);
        $this->assertSame('ogg', $audio->ext);
    }

    /**
     * @param array<string, string> $fileNames file name by file type; a type without one has no file
     */
    private function tune(array $fileNames): zxMusicElement&MockObject
    {
        $tune = $this->createMock(zxMusicElement::class);
        $tune->method('getFileName')->willReturnCallback(
            static fn(string $type): string => $fileNames[$type] ?? '',
        );
        $tune->method('getFileExtension')->willReturnCallback(
            static fn(string $type): string => isset($fileNames[$type]) ? '.' . pathinfo($fileNames[$type], PATHINFO_EXTENSION) : '',
        );
        return $tune;
    }

    /**
     * @return TuneDownloadDto[]
     */
    private function build(zxMusicElement $tune, ?string $trackerFileUrl = null, ?string $mp3Url = null): array
    {
        return (new TuneDownloadsBuilder())->build($tune, self::BASE_URL, null, $trackerFileUrl, $mp3Url);
    }

    /**
     * @param TuneDownloadDto[] $downloads
     */
    private function findKind(array $downloads, TuneDownloadKind $kind): ?TuneDownloadDto
    {
        foreach ($downloads as $download) {
            if ($download->kind === $kind) {
                return $download;
            }
        }
        return null;
    }
}
