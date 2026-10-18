<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Services;

use zxPictureElement;
use ZxArt\Pictures\Dto\PictureDownloadDto;
use ZxArt\Pictures\PictureDownloadKind;

/**
 * The files a picture page offers for download: the original screen, its PC
 * renders, the print version, and the materials — executable and drawing stages.
 */
final readonly class PictureDownloadsBuilder
{
    private const string TYPE_SCA = 'sca';
    private const int MAX_PC_ZOOM = 4;

    /**
     * @return PictureDownloadDto[]
     */
    public function build(zxPictureElement $element, string $baseUrl): array
    {
        $downloads = [];
        if ($element->getFileName('original', false) !== '') {
            $downloads = [...$downloads, ...$this->buildRenders($element, $baseUrl)];
        }

        $exeFileId = $element->getExeFileId();
        if ($exeFileId !== '') {
            $fileName = $element->getFileName('exe', false, false);
            $downloads[] = new PictureDownloadDto(
                id: 'exe',
                kind: PictureDownloadKind::Executable,
                ext: ltrim($element->getFileExtension('exe'), '.') ?: 'exe',
                zoom: null,
                fileName: $fileName,
                size: null,
                url: $baseUrl . 'file/id:' . $exeFileId . '/filename:'
                    . rawurlencode($element->getFileName('exe', true, false)),
            );
        }

        $sequenceFileName = $element->getSequenceFileName();
        if ($sequenceFileName !== '') {
            $downloads[] = new PictureDownloadDto(
                id: 'stages',
                kind: PictureDownloadKind::Stages,
                ext: strtolower(pathinfo($sequenceFileName, PATHINFO_EXTENSION)),
                zoom: null,
                fileName: $sequenceFileName,
                size: null,
                url: $baseUrl . 'file/id:' . $element->getSequenceFileId() . '/filename:' . rawurlencode($sequenceFileName),
            );
        }

        return $downloads;
    }

    /**
     * @return PictureDownloadDto[]
     */
    private function buildRenders(zxPictureElement $element, string $baseUrl): array
    {
        $downloads = [
            new PictureDownloadDto(
                id: 'original',
                kind: PictureDownloadKind::Original,
                ext: ltrim($element->getFileExtension('original'), '.') ?: 'scr',
                zoom: null,
                fileName: null,
                size: null,
                url: $baseUrl . 'file/id:' . $element->getId() . '/filename:'
                    . rawurlencode($element->getFileName('original', true, false)),
            ),
        ];

        $pcFormat = $element->type === self::TYPE_SCA ? 'gif' : 'png';
        for ($zoom = 1; $zoom <= self::MAX_PC_ZOOM; $zoom++) {
            $downloads[] = new PictureDownloadDto(
                id: $pcFormat . '-' . $zoom,
                kind: PictureDownloadKind::Pc,
                ext: $pcFormat,
                zoom: $zoom,
                fileName: null,
                size: null,
                url: $element->getDownloadUrl($zoom),
            );
        }

        $downloads[] = new PictureDownloadDto(
            id: 'print',
            kind: PictureDownloadKind::Print,
            ext: 'png',
            zoom: null,
            fileName: null,
            size: null,
            url: $baseUrl . 'print/id:' . $element->image
                . '/fileName:' . rawurlencode($element->getFileName('image', true, false)) . '/',
        );

        return $downloads;
    }
}
