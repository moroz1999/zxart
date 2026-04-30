<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use fileElement;
use structureManager;
use ZxArt\Prods\Dto\ProdFileDto;
use ZxArt\Prods\Dto\ProdFilesDto;
use ZxArt\Prods\Dto\ProdMapsDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxProdElement;
use zxReleaseElement;

readonly class ProdMediaService
{
    private const string CONNECTED_FILE_SELECTOR = 'connectedFile';
    private const string INLAY_FILES_SELECTOR = 'inlayFilesSelector';
    private const string MAP_FILES_SELECTOR = 'mapFilesSelector';
    private const string RZX_SELECTOR = 'rzx';
    private const string RELEASE_SCREENSHOTS_SELECTOR = 'screenshotsSelector';

    private const string PROD_IMAGE_PRESET = 'prodImage';
    private const string PROD_IMAGE_FULL_PRESET = 'prodImageFull';
    private const string PROD_MAP_IMAGE_PRESET = 'prodMapImage';
    private const string PROD_MAP_IMAGE_FULL_PRESET = 'prodMapImageFull';

    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    public function getProdScreenshots(int $elementId): ProdFilesDto
    {
        $prod = $this->getProd($elementId);
        return new ProdFilesDto(
            files: $this->buildFiles(
                $prod->getFilesList(self::CONNECTED_FILE_SELECTOR),
                self::PROD_IMAGE_PRESET,
                self::PROD_IMAGE_FULL_PRESET,
            ),
        );
    }

    public function getProdInlays(int $elementId): ProdFilesDto
    {
        $prod = $this->getProd($elementId);
        return new ProdFilesDto(
            files: $this->buildFiles(
                $prod->getFilesList(self::INLAY_FILES_SELECTOR),
                self::PROD_IMAGE_PRESET,
                self::PROD_IMAGE_FULL_PRESET,
            ),
        );
    }

    public function getProdMaps(int $elementId): ProdMapsDto
    {
        $prod = $this->getProd($elementId);
        return new ProdMapsDto(
            files: $this->buildFiles(
                $prod->getFilesList(self::MAP_FILES_SELECTOR),
                self::PROD_MAP_IMAGE_PRESET,
                self::PROD_MAP_IMAGE_FULL_PRESET,
            ),
            mapsUrl: $prod->getSpeccyMapsUrl(),
        );
    }

    public function getProdRzx(int $elementId): ProdFilesDto
    {
        $prod = $this->getProd($elementId);
        return new ProdFilesDto(
            files: $this->buildFiles(
                $prod->getFilesList(self::RZX_SELECTOR),
                null,
                null,
            ),
        );
    }

    public function getReleaseScreenshots(int $releaseId): ProdFilesDto
    {
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ProdDetailsException('Release not found', 404);
        }

        return new ProdFilesDto(
            files: $this->buildFiles(
                $release->getFilesList(self::RELEASE_SCREENSHOTS_SELECTOR),
                self::PROD_IMAGE_PRESET,
                self::PROD_IMAGE_FULL_PRESET,
            ),
        );
    }

    private function getProd(int $elementId): zxProdElement
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }
        return $element;
    }

    /**
     * @param iterable<fileElement> $files
     * @return ProdFileDto[]
     */
    private function buildFiles(iterable $files, ?string $preset, ?string $fullPreset): array
    {
        $result = [];
        foreach ($files as $file) {
            if (!$file instanceof fileElement) {
                continue;
            }
            $isImage = $file->isImage();
            $imageUrl = ($isImage && $preset !== null) ? $file->getImageUrl($preset) : null;
            $fullImageUrl = ($isImage && $fullPreset !== null) ? $file->getImageUrl($fullPreset) : null;

            $result[] = new ProdFileDto(
                id: $file->getId(),
                title: $this->decodeText($file->title),
                author: $file->author !== '' ? $this->decodeText($file->author) : null,
                fileName: $file->fileName,
                imageUrl: $imageUrl,
                fullImageUrl: $fullImageUrl,
                downloadUrl: $isImage ? $file->getScreenshotUrl() : $file->getDownloadUrl('view', 'release'),
                isImage: $isImage,
            );
        }
        return $result;
    }

    private function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
