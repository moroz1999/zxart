<?php

declare(strict_types=1);

namespace ZxArt\Tunes\Services;

use zxMusicElement;
use ZxArt\Tunes\Dto\TuneDownloadDto;
use ZxArt\Tunes\TuneDownloadKind;

/**
 * The files a tune page offers for download: the original module, the tracker
 * source, the executable and the recorded audio.
 */
final readonly class TuneDownloadsBuilder
{
    private const string FALLBACK_EXTENSION = 'bin';
    private const string AUDIO_EXTENSION = 'ogg';

    /**
     * @return TuneDownloadDto[]
     */
    public function build(
        zxMusicElement $element,
        string $baseUrl,
        ?string $originalFileUrl,
        ?string $trackerFileUrl,
        ?string $mp3Url,
    ): array {
        $downloads = [];

        if ($originalFileUrl !== null) {
            $downloads[] = $this->fileDownload($element, 'original', TuneDownloadKind::Original, $originalFileUrl);
        }
        if ($trackerFileUrl !== null) {
            $downloads[] = $this->fileDownload($element, 'tracker', TuneDownloadKind::Tracker, $trackerFileUrl);
        }

        $exeFileId = $element->getExeFileId();
        if ($exeFileId !== '') {
            $url = $baseUrl . 'file/id:' . $exeFileId . '/filename:' . $element->getFileName('exe');
            $downloads[] = $this->fileDownload($element, 'exe', TuneDownloadKind::Executable, $url);
        }

        if ($mp3Url !== null) {
            $extension = strtolower(pathinfo($mp3Url, PATHINFO_EXTENSION));
            $downloads[] = new TuneDownloadDto(
                id: 'audio',
                kind: TuneDownloadKind::Audio,
                ext: $extension !== '' ? $extension : self::AUDIO_EXTENSION,
                fileName: null,
                size: null,
                url: $mp3Url,
            );
        }

        return $downloads;
    }

    private function fileDownload(zxMusicElement $element, string $fileType, TuneDownloadKind $kind, string $url): TuneDownloadDto
    {
        $fileName = $element->getFileName($fileType, false, false);

        return new TuneDownloadDto(
            id: $kind->value,
            kind: $kind,
            ext: ltrim($element->getFileExtension($fileType), '.') ?: self::FALLBACK_EXTENSION,
            fileName: $fileName !== '' ? $fileName : null,
            size: null,
            url: $url,
        );
    }
}
