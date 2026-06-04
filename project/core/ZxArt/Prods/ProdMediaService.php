<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use fileElement;
use structureManager;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdFileDto;
use ZxArt\Prods\Dto\ProdFilesDto;
use ZxArt\Prods\Dto\ProdMapsDto;
use ZxArt\Prods\Dto\ProdReleaseInlayDto;
use ZxArt\Prods\Dto\ProdReleaseInlaysDto;
use ZxArt\Prods\Dto\ProdReleaseInstructionFileDto;
use ZxArt\Prods\Dto\ProdReleaseInstructionsDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxProdElement;
use zxReleaseElement;

readonly class ProdMediaService
{

    private const string PROD_IMAGE_PRESET = 'prodImage';
    private const string PROD_IMAGE_FULL_PRESET = 'prodImageFull';
    private const string PROD_MAP_IMAGE_PRESET = 'prodMapImage';
    private const string PROD_MAP_IMAGE_FULL_PRESET = 'prodMapImageFull';

    public function __construct(
        private structureManager $structureManager,
        private ProdInfoBuilder $prodInfoBuilder,
        private ProdElementService $prodElementService,
    ) {
    }

    public function getProdScreenshots(int $elementId): ProdFilesDto
    {
        $prod = $this->prodElementService->get($elementId);
        return new ProdFilesDto(
            files: $this->buildFiles(
                $prod->getFilesList(LinkTypes::CONNECTED_FILE->value),
                self::PROD_IMAGE_PRESET,
                self::PROD_IMAGE_FULL_PRESET,
            ),
        );
    }

    public function getProdInlays(int $elementId): ProdReleaseInlaysDto
    {
        $prod = $this->prodElementService->get($elementId);
        $inlays = [];
        foreach ($prod->getFilesList(LinkTypes::INLAY_FILES_SELECTOR->value) as $file) {
            if (!$file instanceof fileElement) {
                continue;
            }
            $isImage = $file->isImage();
            $imageUrl = $isImage ? $file->getImageUrl(self::PROD_IMAGE_PRESET) : null;
            $fullImageUrl = $isImage ? $file->getImageUrl(self::PROD_IMAGE_FULL_PRESET) : null;

            $inlays[] = new ProdReleaseInlayDto(
                id: $file->getId(),
                title: $this->decodeText($file->title),
                imageUrl: $imageUrl,
                fullImageUrl: $fullImageUrl,
                downloadUrl: $isImage ? $file->getScreenshotUrl() : $file->getDownloadUrl('view', 'release'),
                releaseTitle: '',
                releaseUrl: '',
                releaseYear: 0,
                releaseTypeLabel: null,
                releaseBy: [],
            );
        }
        foreach ($prod->getReleasesList() as $release) {
            $releaseTitle = $this->prodInfoBuilder->decodeText((string)$release->getTitle());
            $releaseUrl = (string)$release->getUrl();
            $releaseYear = $release->getYear() ?? 0;
            $releaseTypeLabel = $release->releaseType !== ''
                ? $this->prodInfoBuilder->translate('zxRelease.type_' . $release->releaseType)
                : null;
            $releaseBy = $this->prodInfoBuilder->buildReleaseBy($release);

            foreach ($release->getFilesList(LinkTypes::INLAY_FILES_SELECTOR->value) as $file) {
                if (!$file instanceof fileElement) {
                    continue;
                }
                $isImage = $file->isImage();
                $imageUrl = $isImage ? $file->getImageUrl(self::PROD_IMAGE_PRESET) : null;
                $fullImageUrl = $isImage ? $file->getImageUrl(self::PROD_IMAGE_FULL_PRESET) : null;

                $inlays[] = new ProdReleaseInlayDto(
                    id: $file->getId(),
                    title: $this->decodeText($file->title),
                    imageUrl: $imageUrl,
                    fullImageUrl: $fullImageUrl,
                    downloadUrl: $isImage ? $file->getScreenshotUrl() : $file->getDownloadUrl('view', 'release'),
                    releaseTitle: $releaseTitle,
                    releaseUrl: $releaseUrl,
                    releaseYear: $releaseYear,
                    releaseTypeLabel: $releaseTypeLabel,
                    releaseBy: $releaseBy,
                );
            }
        }
        return new ProdReleaseInlaysDto(inlays: $inlays);
    }

    public function getProdMaps(int $elementId): ProdMapsDto
    {
        $prod = $this->prodElementService->get($elementId);
        return new ProdMapsDto(
            files: $this->buildFiles(
                $prod->getFilesList(LinkTypes::MAP_FILES_SELECTOR->value),
                self::PROD_MAP_IMAGE_PRESET,
                self::PROD_MAP_IMAGE_FULL_PRESET,
            ),
            mapsUrl: $prod->getSpeccyMapsUrl(),
        );
    }

    public function getProdInstructions(int $elementId): ProdReleaseInstructionsDto
    {
        $prod = $this->prodElementService->get($elementId);
        $files = [];
        foreach ($prod->getReleasesList() as $release) {
            $releaseTitle = $this->prodInfoBuilder->decodeText((string)$release->getTitle());
            $releaseUrl = (string)$release->getUrl();
            $releaseYear = $release->getYear() ?? 0;
            $releaseTypeLabel = $release->releaseType !== ''
                ? $this->prodInfoBuilder->translate('zxRelease.type_' . $release->releaseType)
                : null;
            $releaseBy = $this->prodInfoBuilder->buildReleaseBy($release);

            foreach ($release->getFilesList(LinkTypes::INFO_FILES_SELECTOR->value) as $file) {
                if (!$file instanceof fileElement) {
                    continue;
                }
                $files[] = new ProdReleaseInstructionFileDto(
                    id: $file->getId(),
                    title: $this->decodeText($file->title),
                    fileName: $file->fileName,
                    downloadUrl: $file->getDownloadUrl('view', 'release'),
                    releaseTitle: $releaseTitle,
                    releaseUrl: $releaseUrl,
                    releaseYear: $releaseYear,
                    releaseTypeLabel: $releaseTypeLabel,
                    releaseBy: $releaseBy,
                );
            }
        }
        return new ProdReleaseInstructionsDto(files: $files);
    }

    public function getProdRzx(int $elementId): ProdFilesDto
    {
        $prod = $this->prodElementService->get($elementId);
        return new ProdFilesDto(
            files: $this->buildFiles(
                $prod->getFilesList(LinkTypes::RZX->value),
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

        return $this->buildReleaseScreenshots($release);
    }

    public function buildReleaseScreenshots(zxReleaseElement $release): ProdFilesDto
    {
        return new ProdFilesDto(
            files: $this->buildFiles(
                $release->getFilesList(LinkTypes::SCREENSHOTS_SELECTOR->value),
                self::PROD_IMAGE_PRESET,
                self::PROD_IMAGE_FULL_PRESET,
            ),
        );
    }

    public function buildReleaseScreenshotsWithProdFallback(zxReleaseElement $release): ProdFilesDto
    {
        $own = $this->buildReleaseScreenshots($release);
        if (!empty($own->files)) {
            return $own;
        }
        $prod = $release->getProd();
        if (!$prod instanceof zxProdElement) {
            return $own;
        }
        return $this->getProdScreenshots($prod->getId());
    }

    public function buildReleaseInlays(zxReleaseElement $release): ProdReleaseInlaysDto
    {
        $releaseTitle = $this->prodInfoBuilder->decodeText((string)$release->getTitle());
        $releaseUrl = (string)$release->getUrl();
        $releaseYear = $release->getYear() ?? 0;
        $releaseTypeLabel = $release->releaseType !== ''
            ? $this->prodInfoBuilder->translate('zxRelease.type_' . $release->releaseType)
            : null;
        $releaseBy = $this->prodInfoBuilder->buildReleaseBy($release);

        $inlays = [];
        foreach ($release->getFilesList(LinkTypes::INLAY_FILES_SELECTOR->value) as $file) {
            if (!$file instanceof fileElement) {
                continue;
            }
            $isImage = $file->isImage();
            $imageUrl = $isImage ? $file->getImageUrl(self::PROD_IMAGE_PRESET) : null;
            $fullImageUrl = $isImage ? $file->getImageUrl(self::PROD_IMAGE_FULL_PRESET) : null;

            $inlays[] = new ProdReleaseInlayDto(
                id: $file->getId(),
                title: $this->decodeText($file->title),
                imageUrl: $imageUrl,
                fullImageUrl: $fullImageUrl,
                downloadUrl: $isImage ? $file->getScreenshotUrl() : $file->getDownloadUrl('view', 'release'),
                releaseTitle: $releaseTitle,
                releaseUrl: $releaseUrl,
                releaseYear: $releaseYear,
                releaseTypeLabel: $releaseTypeLabel,
                releaseBy: $releaseBy,
            );
        }
        return new ProdReleaseInlaysDto(inlays: $inlays);
    }

    public function buildReleaseInstructions(zxReleaseElement $release): ProdReleaseInstructionsDto
    {
        $releaseTitle = $this->prodInfoBuilder->decodeText((string)$release->getTitle());
        $releaseUrl = (string)$release->getUrl();
        $releaseYear = $release->getYear() ?? 0;
        $releaseTypeLabel = $release->releaseType !== ''
            ? $this->prodInfoBuilder->translate('zxRelease.type_' . $release->releaseType)
            : null;
        $releaseBy = $this->prodInfoBuilder->buildReleaseBy($release);

        $files = [];
        foreach ($release->getFilesList(LinkTypes::INFO_FILES_SELECTOR->value) as $file) {
            if (!$file instanceof fileElement) {
                continue;
            }
            $files[] = new ProdReleaseInstructionFileDto(
                id: $file->getId(),
                title: $this->decodeText($file->title),
                fileName: $file->fileName,
                downloadUrl: $file->getDownloadUrl('view', 'release'),
                releaseTitle: $releaseTitle,
                releaseUrl: $releaseUrl,
                releaseYear: $releaseYear,
                releaseTypeLabel: $releaseTypeLabel,
                releaseBy: $releaseBy,
            );
        }
        return new ProdReleaseInstructionsDto(files: $files);
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
