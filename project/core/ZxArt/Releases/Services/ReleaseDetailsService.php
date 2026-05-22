<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\Prods\Dto\ProdCategoryPathDto;
use ZxArt\Prods\Dto\ProdCategoryRefDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdVotingDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\ProdInfoBuilder;
use ZxArt\Prods\ProdMediaService;
use ZxArt\Releases\Dto\ReleaseDetailsDto;
use ZxArt\Releases\Dto\ReleaseProdRefDto;
use ZxArt\Releases\Dto\ReleaseTabsDto;
use ZxArt\Shared\EntityType;
use zxProdCategoryElement;
use zxProdElement;
use zxReleaseElement;

readonly class ReleaseDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private ProdInfoBuilder $infoBuilder,
        private ProdMediaService $prodMediaService,
        private ReleaseFormatsProvider $releaseFormatsProvider,
    ) {
    }

    public function getDetails(int $releaseId): ReleaseDetailsDto
    {
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ProdDetailsException('Release not found', 404);
        }

        $theme = $this->infoBuilder->resolveCurrentTheme();
        $isDownloadable = $release->isDownloadable();
        $isPlayable = $release->isPlayable();
        $emulatorType = $release->getEmulatorType();

        $screenshots = $this->prodMediaService->buildReleaseScreenshots($release);
        $inlays = $this->prodMediaService->buildReleaseInlays($release);
        $instructions = $this->prodMediaService->buildReleaseInstructions($release);

        return new ReleaseDetailsDto(
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
            authors: $this->infoBuilder->buildReleaseAuthors($release),
            publishers: $this->infoBuilder->buildReleasePublishers($release),
            formats: $this->buildFormats($release),
            isDownloadable: $isDownloadable,
            isPlayable: $isPlayable,
            downloadUrl: $isDownloadable && $release->fileName !== '' ? $release->getFileUrl() : null,
            playUrl: $isPlayable ? $release->getPlayUrl($emulatorType === 'usp') : null,
            fileName: $release->fileName !== '' ? $release->fileName : null,
            emulatorType: $emulatorType,
            prodLegalStatus: $release->getLegalStatus(),
            prodExternalLink: '',
            downloadsCount: $release->getDownloadsCount(),
            playsCount: $release->getPlaysCount(),
            externalLinks: $this->infoBuilder->buildLinks($release, $theme),
            screenshots: $screenshots->files,
            prod: $this->buildProdRef($release),
            inlays: $inlays->inlays,
            instructions: $instructions->files,
            votes: $this->buildVoting($release),
            tabs: new ReleaseTabsDto(
                hasScreenshots: count($screenshots->files) > 0,
                hasInlays: count($inlays->inlays) > 0,
                hasInstructions: count($instructions->files) > 0,
            ),
        );
    }

    private function buildProdRef(zxReleaseElement $release): ReleaseProdRefDto
    {
        $prod = $release->getProd();

        if (!$prod instanceof zxProdElement) {
            return new ReleaseProdRefDto(
                id: 0,
                title: '',
                url: '',
                year: 0,
                authorNames: [],
                thumbnailUrl: null,
                categoriesPaths: [],
            );
        }

        return new ReleaseProdRefDto(
            id: $prod->getId(),
            title: $this->infoBuilder->decodeText($prod->title),
            url: (string)$prod->getUrl(),
            year: $prod->year,
            authorNames: $this->buildProdAuthorNames($prod),
            thumbnailUrl: $prod->getImageUrl(1) ?: null,
            categoriesPaths: $this->buildCategoriesPaths($prod),
        );
    }

    /**
     * @return string[]
     */
    private function buildProdAuthorNames(zxProdElement $prod): array
    {
        $names = [];
        foreach ($prod->getAuthorsInfo(EntityType::Prod->value) as $info) {
            $authorElement = $info['authorElement'];
            if (!$authorElement instanceof authorElement && !$authorElement instanceof authorAliasElement) {
                continue;
            }
            $names[] = $this->infoBuilder->decodeText($authorElement->title);
        }
        return $names;
    }

    /**
     * @return ProdCategoryPathDto[]
     */
    private function buildCategoriesPaths(zxProdElement $prod): array
    {
        $paths = [];
        foreach ($prod->getCategoriesPaths() as $rawPath) {
            $categories = [];
            foreach ($rawPath as $category) {
                if (!$category instanceof zxProdCategoryElement) {
                    continue;
                }
                $categories[] = new ProdCategoryRefDto(
                    id: $category->getId(),
                    title: $this->infoBuilder->decodeText($category->title),
                    url: (string)$category->getUrl(),
                );
            }
            if ($categories) {
                $paths[] = new ProdCategoryPathDto(categories: $categories);
            }
        }
        return $paths;
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

    private function buildVoting(zxReleaseElement $release): ProdVotingDto
    {
        return new ProdVotingDto(
            votes: $release->getVotes(),
            votesAmount: $release->getVotesAmount(),
            userVote: null,
            denyVoting: (bool)$release->denyVoting,
            votePercent: null,
        );
    }
}
