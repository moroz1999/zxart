<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use DesignTheme;
use structureManager;
use ZxArt\Prods\Dto\ProdGroupRefDto;
use ZxArt\Prods\Dto\ProdReleaseDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdReleasesDto;
use ZxArt\Prods\Dto\ProdVotingDto;
use ZxArt\Releases\Repositories\ReleasesRepository;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use zxReleaseElement;

readonly class ProdReleasesService
{
    public function __construct(
        private ProdElementService $prodElementService,
        private ProdInfoBuilder $infoBuilder,
        private ReleaseFormatsProvider $releaseFormatsProvider,
        private ProdMediaService $prodMediaService,
        private ReleasesRepository $releasesRepository,
        private structureManager $structureManager,
    ) {
    }

    public function getReleases(int $elementId): ProdReleasesDto
    {
        $element = $this->prodElementService->get($elementId);

        $theme = $this->infoBuilder->resolveCurrentTheme();
        $prodLegalStatus = $element->getLegalStatus();
        $prodExternalLink = $element->externalLink;

        $releases = [];
        foreach ($element->getReleasesList() as $release) {
            $releases[] = $this->buildRelease($release, $prodLegalStatus, $prodExternalLink, $theme);
        }

        return new ProdReleasesDto(releases: $releases);
    }

    /**
     * @return ProdReleaseDto[]
     */
    public function getLatestAdded(int $limit): array
    {
        $ids = $this->releasesRepository->getLatestAddedIds($limit);
        $theme = $this->infoBuilder->resolveCurrentTheme();
        $result = [];
        foreach ($ids as $id) {
            $element = $this->structureManager->getElementById($id);
            if ($element instanceof zxReleaseElement) {
                $result[] = $this->buildRelease($element, '', '', $theme);
            }
        }
        return $result;
    }

    public function buildStandaloneRelease(zxReleaseElement $release): ProdReleaseDto
    {
        return $this->buildRelease($release, '', '', $this->infoBuilder->resolveCurrentTheme());
    }

    private function buildRelease(
        zxReleaseElement $release,
        string $prodLegalStatus,
        string $prodExternalLink,
        ?DesignTheme $theme,
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
            voting: $this->buildReleaseVoting($release),
            externalLinks: $this->infoBuilder->buildLinks($release, $theme),
            screenshots: $this->prodMediaService->buildReleaseScreenshotsWithProdFallback($release)->files,
        );
    }

    private function buildReleaseVoting(zxReleaseElement $release): ProdVotingDto
    {
        $denyVoting = $release->isVotingDenied();
        $userVoteRaw = $release->getUserVote();
        $userVote = $userVoteRaw === null || $userVoteRaw === '' ? null : (int)$userVoteRaw;

        return new ProdVotingDto(
            votes: $release->getVotes(),
            votesAmount: $release->getVotesAmount(),
            userVote: $userVote,
            denyVoting: $denyVoting,
            votePercent: $denyVoting ? null : (float)$release->getVotePercent(),
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
