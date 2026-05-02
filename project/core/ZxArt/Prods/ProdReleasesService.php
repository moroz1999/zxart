<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use authorElement;
use DesignTheme;
use groupElement;
use structureManager;
use ZxArt\Prods\Dto\ProdGroupRefDto;
use ZxArt\Prods\Dto\ProdReleaseDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdReleasesDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use zxProdElement;
use zxReleaseElement;

readonly class ProdReleasesService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdInfoBuilder $infoBuilder,
        private ReleaseFormatsProvider $releaseFormatsProvider,
    ) {
    }

    public function getReleases(int $elementId): ProdReleasesDto
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }

        $theme = $this->infoBuilder->resolveCurrentTheme();
        $prodLegalStatus = $element->getLegalStatus();
        $prodExternalLink = $element->externalLink;

        $releases = [];
        foreach ($element->getReleasesList() as $release) {
            $releases[] = $this->buildRelease($release, $prodLegalStatus, $prodExternalLink, $theme);
        }

        return new ProdReleasesDto(releases: $releases);
    }

    private function buildRelease(
        zxReleaseElement $release,
        string $prodLegalStatus,
        string $prodExternalLink,
        ?DesignTheme $theme,
    ): ProdReleaseDto {
        $isDownloadable = $release->isDownloadable();
        $isPlayable = $release->isPlayable();

        return new ProdReleaseDto(
            id: $release->getId(),
            title: $this->infoBuilder->decodeText((string)$release->getTitle()),
            url: (string)$release->getUrl(),
            year: $release->getYear() ?? 0,
            version: $release->version,
            releaseType: $release->releaseType,
            releaseTypeLabel: $release->releaseType !== ''
                ? $this->infoBuilder->translate('zxRelease.type_' . $release->releaseType)
                : null,
            hardwareRequired: $release->hardwareRequired,
            description: (string)$release->description,
            isRealtime: $release->isRealtime(),
            party: $this->infoBuilder->buildParty($release),
            languages: $this->infoBuilder->buildLanguages($release),
            hardware: $this->infoBuilder->buildHardware($release),
            releaseBy: $this->buildReleaseBy($release),
            formats: $this->buildFormats($release),
            isDownloadable: $isDownloadable,
            isPlayable: $isPlayable,
            downloadUrl: $isDownloadable && $release->fileName !== '' ? $release->getFileUrl() : null,
            playUrl: $isPlayable ? $release->getPlayUrl() : null,
            fileName: $release->fileName !== '' ? $release->fileName : null,
            emulatorType: $release->getEmulatorType(),
            prodLegalStatus: $prodLegalStatus,
            prodExternalLink: $prodExternalLink,
            downloadsCount: $release->getDownloadsCount(),
            playsCount: $release->getPlaysCount(),
            externalLinks: $this->infoBuilder->buildLinks($release, $theme),
        );
    }

    /**
     * @return ProdGroupRefDto[]
     */
    private function buildReleaseBy(zxReleaseElement $release): array
    {
        $refs = [];
        foreach ($release->getReleaseBy() as $element) {
            if (!$element instanceof groupElement && !$element instanceof authorElement) {
                continue;
            }
            $refs[] = new ProdGroupRefDto(
                id: $element->getId(),
                title: $this->infoBuilder->decodeText($element->title),
                url: (string)$element->getUrl(),
            );
        }
        return $refs;
    }

    /**
     * @return ProdReleaseFormatDto[]
     */
    private function buildFormats(zxReleaseElement $release): array
    {
        $formats = [];
        foreach ($release->releaseFormat as $format) {
            if ($format === '') {
                continue;
            }
            $formats[] = new ProdReleaseFormatDto(
                format: $format,
                label: $this->infoBuilder->translate('zxRelease.filetype_' . $format),
                emoji: $this->releaseFormatsProvider->getFormatEmoji($format),
                catalogueUrl: $release->getCatalogueUrlByFiletype($format),
            );
        }
        return $formats;
    }
}
