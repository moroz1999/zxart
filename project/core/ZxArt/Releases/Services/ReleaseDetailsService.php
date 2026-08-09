<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use authorAliasElement;
use authorElement;
use controller;
use privilegesManager;
use structureManager;
use userElement;
use ZxArt\PictureList\PictureListService;
use ZxArt\Prods\Dto\ProdCategoryPathDto;
use ZxArt\Prods\Dto\ProdCategoryRefDto;
use ZxArt\Prods\Dto\ProdReleaseFormatDto;
use ZxArt\Prods\Dto\ProdVotingDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Prods\Dto\ProdSubmitterDto;
use ZxArt\Prods\ProdInfoBuilder;
use ZxArt\Prods\Services\ProdHardwareService;
use ZxArt\Prods\ProdMediaService;
use ZxArt\Releases\Dto\ReleaseDetailsDto;
use ZxArt\Releases\Dto\ReleaseFileStructureItemDto;
use ZxArt\Releases\Dto\ReleaseProdRefDto;
use ZxArt\Releases\Dto\ReleaseTabsDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\DescriptionFormatter;
use ZxArt\Shared\StructureType;
use ZxArt\Urls\EntityUrlResolver;
use zxProdCategoryElement;
use zxProdElement;
use zxReleaseElement;

readonly class ReleaseDetailsService
{
    public function __construct(
        private EntityUrlResolver $entityUrlResolver,
        private structureManager $structureManager,
        private ProdInfoBuilder $infoBuilder,
        private ProdHardwareService $prodHardwareService,
        private DescriptionFormatter $descriptionFormatter,
        private ProdMediaService $prodMediaService,
        private ReleaseFormatsProvider $releaseFormatsProvider,
        private controller $controller,
        private privilegesManager $privilegesManager,
        private PictureListService $pictureListService,
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
        $releaseUrl = $this->entityUrlResolver->urlFor($release);
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
        $covers = $this->prodMediaService->buildReleaseCovers($release);
        $instructions = $this->prodMediaService->buildReleaseInstructions($release);

        $fileStructure = $isDownloadable ? $this->buildFileStructure($release) : [];
        $hasPictures = $this->pictureListService->countReleasePictures($release->getId()) > 0;

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
            description: $this->descriptionFormatter->decode((string)$release->description),
            isRealtime: $release->isRealtime(),
            party: $this->infoBuilder->buildParty($release),
            languages: $this->infoBuilder->buildLanguages($release),
            hardware: $this->infoBuilder->buildHardware($release),
            // What the release actually inherits, not everything the production
            // holds: a category the release speaks about is not inherited at all,
            // so showing the production's codes there would put a chip on the page
            // the release does not carry. Nothing needs subtracting afterwards —
            // what comes back cannot overlap the release's own codes.
            prodHardware: $this->infoBuilder->buildHardwareFromCodes(
                $this->prodHardwareService->getInheritedApplicable(
                    $release->getPersistedId(),
                    $release->getHardwareCodes(),
                ),
            ),
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
            prodExternalLink: $release->getProd()?->externalLink ?? '',
            downloadsCount: $release->getDownloadsCount(),
            playsCount: $release->getPlaysCount(),
            externalLinks: $this->infoBuilder->buildLinks($release, $theme),
            screenshots: $screenshots->files,
            prod: $this->buildProdRef($release),
            covers: $covers->groups,
            instructions: $instructions->files,
            votes: $this->buildVoting($release),
            tabs: new ReleaseTabsDto(
                hasScreenshots: count($screenshots->files) > 0,
                hasCovers: count($covers->groups) > 0,
                hasInstructions: count($instructions->files) > 0,
                hasStructure: count($fileStructure) > 0,
                hasPictures: $hasPictures,
            ),
            fileStructure: $fileStructure,
            canUploadScreenshot: $canUploadScreenshot,
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
            url: $this->entityUrlResolver->urlForUser($user),
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
            url: $this->entityUrlResolver->urlFor($prod),
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

        foreach ($items as $item) {
            $isFolder = $item['type'] === 'folder';
            $viewable = (bool)$item['viewable'];

            $downloadUrl = !$isFolder
                ? $baseUrl . 'zxfile/id:' . $releaseId . '/fileId:' . $item['id'] . '/' . rawurlencode($item['fileName'])
                : null;

            $children = isset($item['items']) ? $this->buildFileStructureItems($item['items'], $release) : [];

            $result[] = new ReleaseFileStructureItemDto(
                id: (int)$item['id'],
                fileName: $this->decodeFileNameForDisplay((string)$item['fileName']),
                size: (int)$item['size'],
                type: (string)$item['type'],
                typeLabel: $this->infoBuilder->translate('zxrelease.filetype_' . $item['type']),
                viewable: $viewable,
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
