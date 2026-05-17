<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use DesignTheme;
use ZxArt\Prods\Dto\ProdGroupRefDto;
use ZxArt\Prods\Dto\ProdReleaseDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdReleasesDto;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use zxReleaseElement;

readonly class ProdReleasesService
{
    public function __construct(
        private ProdElementService $prodElementService,
        private ProdInfoBuilder $infoBuilder,
        private ReleaseFormatsProvider $releaseFormatsProvider,
        private ProdMediaService $prodMediaService,
    ) {
    }

    public function getReleases(int $elementId): ProdReleasesDto
    {
        $element = $this->prodElementService->get($elementId);

        $theme = $this->infoBuilder->resolveCurrentTheme();
        $prodLegalStatus = $element->getLegalStatus();
        $prodExternalLink = $element->externalLink;
        $prodRating = $element->getVotes();

        $releases = [];
        foreach ($element->getReleasesList() as $release) {
            $releases[] = $this->buildRelease($release, $prodLegalStatus, $prodExternalLink, $theme, $prodRating);
        }

        return new ProdReleasesDto(releases: $releases);
    }

    private function buildRelease(
        zxReleaseElement $release,
        string $prodLegalStatus,
        string $prodExternalLink,
        ?DesignTheme $theme,
        float $prodRating,
    ): ProdReleaseDto {
        $isDownloadable = $release->isDownloadable();
        $isPlayable = $release->isPlayable();
        $emulatorType = $release->getEmulatorType();

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
            playUrl: $isPlayable ? $release->getPlayUrl($emulatorType === 'usp') : null,
            fileName: $release->fileName !== '' ? $release->fileName : null,
            emulatorType: $emulatorType,
            prodLegalStatus: $prodLegalStatus,
            prodExternalLink: $prodExternalLink,
            downloadsCount: $release->getDownloadsCount(),
            playsCount: $release->getPlaysCount(),
            rating: $prodRating,
            externalLinks: $this->infoBuilder->buildLinks($release, $theme),
            screenshots: $this->prodMediaService->buildReleaseScreenshots($release)->files,
        );
    }

    /**
     * @return ProdGroupRefDto[]
     */
    private function buildReleaseBy(zxReleaseElement $release): array
    {
        return $this->infoBuilder->buildReleaseBy($release);
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
