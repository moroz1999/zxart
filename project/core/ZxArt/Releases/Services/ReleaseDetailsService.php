<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use authorAliasElement;
use authorElement;
use controller;
use privilegesManager;
use structureManager;
use userElement;
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Prods\Dto\ProdCategoryPathDto;
use ZxArt\Prods\Dto\ProdCategoryRefDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdVotingDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Dto\ProdSubmitterDto;
use ZxArt\Prods\ProdInfoBuilder;
use ZxArt\Prods\ProdMediaService;
use ZxArt\Releases\Dto\ReleaseDetailsDto;
use ZxArt\Releases\Dto\ReleaseFileStructureItemDto;
use ZxArt\Releases\Dto\ReleaseProdRefDto;
use ZxArt\Releases\Dto\ReleaseTabsDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
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
        private controller $controller,
        private ZxParsingManager $zxParsingManager,
        private privilegesManager $privilegesManager,
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
        $releaseUrl = (string)$release->getUrl();
        $canUploadScreenshot = $this->privilegesManager->checkPrivilegesForAction(
            $release->getId(),
            'uploadScreenshot',
            StructureType::ZxRelease->value,
        ) === true;
        $canReorderScreenshots = $this->privilegesManager->checkPrivilegesForAction(
            $release->getId(),
            'publicReceive',
            StructureType::ZxRelease->value,
        ) === true;

        $screenshots = $this->prodMediaService->buildReleaseScreenshots($release);
        $inlays = $this->prodMediaService->buildReleaseInlays($release);
        $instructions = $this->prodMediaService->buildReleaseInstructions($release);

        $fileStructure = $this->buildFileStructure($release);

        return new ReleaseDetailsDto(
            id: $release->getId(),
            title: $this->infoBuilder->decodeText((string)$release->getTitle()),
            url: $releaseUrl,
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
            fileName: $release->fileName !== '' ? $this->decodeFileNameForDisplay($release->fileName) : null,
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
                hasStructure: count($fileStructure) > 0,
            ),
            fileStructure: $fileStructure,
            canUploadScreenshot: $canUploadScreenshot,
            screenshotUploadUrl: $releaseUrl . 'id:' . $release->getId() . '/action:uploadScreenshot/',
            canReorderScreenshots: $canReorderScreenshots,
            dateCreated: $release->dateCreated,
            submitter: $this->buildSubmitter($release),
        );
    }

    private function buildSubmitter(zxReleaseElement $release): ?ProdSubmitterDto
    {
        $user = $release->getUserElement();
        if (!$user instanceof userElement) {
            return null;
        }

        return new ProdSubmitterDto(
            id: $user->getId(),
            userName: $this->infoBuilder->decodeText($user->userName),
            url: (string)$user->getUrl(),
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
        $userVoteRaw = $release->getUserVote();
        return new ProdVotingDto(
            votes: $release->getVotes(),
            votesAmount: $release->getVotesAmount(),
            userVote: $userVoteRaw !== null && $userVoteRaw !== false ? (int)$userVoteRaw : null,
            denyVoting: (bool)$release->denyVoting,
            votePercent: null,
        );
    }

    /**
     * @param zxReleaseElement $release
     * @return ReleaseFileStructureItemDto[]
     */
    private function buildFileStructure(zxReleaseElement $release): array
    {
        if (!$release->parsed) {
            return [];
        }

        $structure = $release->getReleaseStructure();
        if (!$structure) {
            return [];
        }

        return $this->buildFileStructureItems($structure, $release);
    }

    /**
     * @param array $items
     * @param zxReleaseElement $release
     * @return ReleaseFileStructureItemDto[]
     */
    private function buildFileStructureItems(array $items, zxReleaseElement $release): array
    {
        $result = [];
        $baseUrl = (string)$this->controller->baseURL;
        $releaseId = $release->getId();
        $releaseUrl = (string)$release->getUrl();

        foreach ($items as $item) {
            $isFolder = $item['type'] === 'folder';
            $viewable = (bool)$item['viewable'];

            $downloadUrl = !$isFolder
                ? $baseUrl . 'zxfile/id:' . $releaseId . '/fileId:' . $item['id'] . '/' . rawurlencode($item['fileName'])
                : null;

            $viewUrl = (!$isFolder && $viewable)
                ? $releaseUrl . 'action:viewFile/id:' . $releaseId . '/fileId:' . $item['id'] . '/'
                : null;

            $children = isset($item['items']) ? $this->buildFileStructureItems($item['items'], $release) : [];

            $result[] = new ReleaseFileStructureItemDto(
                id: (int)$item['id'],
                fileName: $this->decodeFileNameForDisplay((string)$item['fileName']),
                size: (int)$item['size'],
                type: (string)$item['type'],
                typeLabel: $this->infoBuilder->translate('zxrelease.filetype_' . $item['type']),
                viewable: $viewable,
                viewUrl: $viewUrl,
                downloadUrl: $downloadUrl,
                items: $children,
            );
        }

        return $result;
    }

    private function decodeFileNameForDisplay(string $fileName): string
    {
        if (!preg_match('/%[0-9A-Fa-f]{2}|\+/', $fileName)) {
            return $fileName;
        }

        return urldecode($fileName);
    }
}
