<?php

declare(strict_types=1);

namespace ZxArt\Pictures\Services;

use controller;
use structureManager;
use translationsManager;
use ZxArt\PictureList\PictureListService;
use ZxArt\Pictures\Dto\PictureDetailsDto;
use ZxArt\Pictures\Dto\PictureDownloadDto;
use ZxArt\Pictures\Dto\PictureDto;
use ZxArt\Pictures\Dto\PictureMaterialDto;
use ZxArt\Pictures\Dto\PictureMentionDto;
use ZxArt\Pictures\Dto\PicturePartyContextDto;
use ZxArt\Pictures\Dto\PictureProdContextDto;
use ZxArt\Pictures\Dto\PictureRelatedRailDto;
use ZxArt\Pictures\Dto\PictureSubmitterDto;
use ZxArt\Pictures\Dto\PictureTagDto;
use ZxArt\Pictures\Exception\PictureDetailsException;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Prods\ProdInfoBuilder;
use ZxArt\Shared\Dto\AuthorDto;
use zxPictureElement;

/**
 * Builds the rich {@see PictureDetailsDto} consumed by the Angular
 * <zx-picture-details> page. The controller maps it to its REST DTO.
 */
readonly class PictureDetailsService
{
    public function __construct(
        private structureManager $structureManager,
        private controller $controller,
        private translationsManager $translationsManager,
        private PicturesTransformer $picturesTransformer,
        private PictureListService $pictureListService,
        private ProdInfoBuilder $infoBuilder,
    ) {
    }

    public function getDetails(int $pictureId): PictureDetailsDto
    {
        $element = $this->structureManager->getElementById($pictureId);
        if (!$element instanceof zxPictureElement) {
            throw new PictureDetailsException('Picture not found', 404);
        }

        $picture = $this->picturesTransformer->toDto($element);
        $baseUrl = (string)$this->controller->baseURL;

        return new PictureDetailsDto(
            id: $picture->id,
            title: $picture->title,
            url: $picture->url,
            imageUrl: $picture->imageUrl,
            largeImageUrl: $picture->largeImageUrl,
            fileId: $picture->fileId,
            type: $picture->type,
            pictureBorder: $picture->pictureBorder,
            palette: $picture->palette,
            rotation: $picture->rotation,
            year: $picture->year,
            authors: $picture->authors,
            party: $picture->party,
            release: $picture->release,
            isRealtime: $picture->isRealtime,
            isFlickering: $picture->isFlickering,
            compo: $picture->compo,
            votes: $picture->votes,
            votesAmount: $picture->votesAmount,
            userVote: $picture->userVote,
            denyVoting: $picture->denyVoting,
            commentsAmount: $picture->commentsAmount,
            description: $element->description
                ? $this->infoBuilder->decodeText((string)$element->description)
                : null,
            originalAuthors: $this->buildAuthors($element->getOriginalAuthorsList() ?: []),
            tags: $this->buildTags($element),
            partyContext: $this->buildPartyContext($element),
            prodContext: $this->buildProdContext($element),
            formatLabel: (string)$this->translationsManager->getTranslationByName(
                $element->getZxPictureTypeTranslation($element->type)
            ),
            paletteLabel: (string)$this->translationsManager->getTranslationByName(
                'zxpicture.palette_' . $element->getPalette()
            ),
            resolution: null,
            originalName: $element->originalName ? urldecode((string)$element->originalName) : null,
            views: (int)$element->views,
            submitter: $this->buildSubmitter($element),
            dateCreated: $element->dateCreated ? (string)$element->dateCreated : null,
            downloads: $this->buildDownloads($element, $baseUrl),
            materials: $this->buildMaterials($element, $baseUrl),
            techInfo: [],
            sequenceUrl: $element->sequenceName
                ? $baseUrl . 'file/id:' . $element->sequence . '/filename:' . rawurlencode((string)$element->sequenceName)
                : null,
            mentions: $this->buildMentions($element),
            related: $this->buildRelated($element),
        );
    }

    /**
     * @param iterable<mixed> $authors
     * @return AuthorDto[]
     */
    private function buildAuthors(iterable $authors): array
    {
        $result = [];
        foreach ($authors as $author) {
            $result[] = new AuthorDto(
                name: $this->infoBuilder->decodeText((string)$author->getTitle()),
                url: (string)$author->getUrl(),
            );
        }
        return $result;
    }

    /**
     * @return PictureTagDto[]
     */
    private function buildTags(zxPictureElement $element): array
    {
        $result = [];
        foreach (($element->getTagsList() ?: []) as $tag) {
            $result[] = new PictureTagDto(
                title: $this->infoBuilder->decodeText((string)$tag->getTitle()),
                url: (string)$tag->getUrl(),
            );
        }
        return $result;
    }

    private function buildPartyContext(zxPictureElement $element): ?PicturePartyContextDto
    {
        $party = $element->getPartyElement();
        if ($party === null) {
            return null;
        }
        $compoLabel = null;
        if ($element->compo) {
            $compoLabel = (string)$this->translationsManager->getTranslationByName('zxPicture.compo_' . $element->compo);
        }
        return new PicturePartyContextDto(
            title: $this->infoBuilder->decodeText((string)$party->getTitle()),
            url: (string)$party->getUrl(),
            place: (int)$element->partyplace ?: null,
            compoLabel: $compoLabel,
        );
    }

    private function buildProdContext(zxPictureElement $element): ?PictureProdContextDto
    {
        $release = $element->getReleaseElement();
        if ($release === null) {
            return null;
        }
        return new PictureProdContextDto(
            title: $this->infoBuilder->decodeText((string)$release->getTitle()),
            url: (string)$release->getUrl(),
            year: $release->year ? (string)$release->year : null,
            kindLabel: (string)$this->translationsManager->getTranslationByName('zxpicture.release'),
        );
    }

    private function buildSubmitter(zxPictureElement $element): ?PictureSubmitterDto
    {
        $user = $element->getUserElement();
        if (!$user) {
            return null;
        }
        return new PictureSubmitterDto(
            userName: $this->infoBuilder->decodeText((string)$user->userName),
            url: (string)$user->getUrl(),
        );
    }

    /**
     * @return PictureDownloadDto[]
     */
    private function buildDownloads(zxPictureElement $element, string $baseUrl): array
    {
        $downloads = [];

        if ($element->getFileName('original', false)) {
            $downloads[] = new PictureDownloadDto(
                id: 'original',
                ext: ltrim($element->getFileExtension('original'), '.') ?: 'scr',
                label: (string)$this->translationsManager->getTranslationByName('field.originalfile'),
                sub: null,
                size: null,
                url: $baseUrl . 'file/id:' . $element->getId() . '/filename:' . rawurlencode((string)$element->getFileName()),
            );

            for ($zoom = 1; $zoom <= 4; $zoom++) {
                $downloads[] = new PictureDownloadDto(
                    id: 'png-' . $zoom,
                    ext: 'png',
                    label: (string)$this->translationsManager->getTranslationByName('zxpicture.download_pc'),
                    sub: $zoom . '×',
                    size: null,
                    url: $element->getDownloadUrl($zoom),
                );
            }

            $downloads[] = new PictureDownloadDto(
                id: 'print',
                ext: 'png',
                label: (string)$this->translationsManager->getTranslationByName('zxpicture.download_print'),
                sub: null,
                size: null,
                url: $baseUrl . 'print/id:' . $element->image
                    . '/fileName:' . rawurlencode((string)$element->getFileName('image', true, true)) . '/',
            );
        }

        if ($element->exeFile) {
            $downloads[] = new PictureDownloadDto(
                id: 'exe',
                ext: ltrim($element->getFileExtension('exe'), '.') ?: 'exe',
                label: (string)$element->getFileName('exe', false, false),
                sub: null,
                size: null,
                url: $baseUrl . 'file/id:' . $element->exeFile . '/filename:' . rawurlencode((string)$element->getFileName('exe')),
            );
        }

        return $downloads;
    }

    /**
     * @return PictureMaterialDto[]
     */
    private function buildMaterials(zxPictureElement $element, string $baseUrl): array
    {
        $materials = [];
        if ($element->inspiredName) {
            $materials[] = new PictureMaterialDto(
                id: 'inspired',
                kind: 'inspiration',
                label: (string)$element->inspiredName,
                imageUrl: $baseUrl . 'image/type:inspiredImage/id:' . $element->inspired
                    . '/filename:' . rawurlencode((string)$element->inspiredName),
            );
        }
        if ($element->inspired2Name) {
            $materials[] = new PictureMaterialDto(
                id: 'inspired2',
                kind: 'inspiration',
                label: (string)$element->inspired2Name,
                imageUrl: $baseUrl . 'image/type:inspired2Image/id:' . $element->inspired2
                    . '/filename:' . rawurlencode((string)$element->inspired2Name),
            );
        }
        return $materials;
    }

    /**
     * @return PictureMentionDto[]
     */
    private function buildMentions(zxPictureElement $element): array
    {
        $result = [];
        foreach ($element->getPressMentions() as $article) {
            $result[] = new PictureMentionDto(
                title: $this->infoBuilder->decodeText((string)$article->getTitle()),
                url: (string)$article->getUrl(),
            );
        }
        return $result;
    }

    /**
     * Builds up to three related rails: from the prod, by the author, by tags.
     *
     * @return PictureRelatedRailDto[]
     */
    private function buildRelated(zxPictureElement $element): array
    {
        $rails = [];
        $currentId = $element->getId();

        $release = $element->getReleaseElement();
        if ($release !== null) {
            $items = $this->limitPictures(
                $this->pictureListService->getReleasePictures($release->getId()),
                $currentId,
                6
            );
            if ($items) {
                $rails[] = new PictureRelatedRailDto(
                    kind: 'prod',
                    title: (string)$this->translationsManager->getTranslationByName('picture.morefromgame'),
                    kicker: $release->year ? (string)$release->year : null,
                    items: $items,
                );
            }
        }

        $authorPictures = (array)($element->getBestAuthorsPictures(6) ?? []);
        $items = $this->limitPictures($authorPictures, $currentId, 6);
        if ($items) {
            $rails[] = new PictureRelatedRailDto(
                kind: 'author',
                title: (string)$this->translationsManager->getTranslationByName('picture.morefromauthor'),
                kicker: null,
                items: $items,
            );
        }

        $tagRail = $this->buildTagRail($element, $currentId);
        if ($tagRail !== null) {
            $rails[] = $tagRail;
        }

        return $rails;
    }

    private function buildTagRail(zxPictureElement $element, int $currentId): ?PictureRelatedRailDto
    {
        $tags = $element->getTagsList();
        if (!$tags) {
            return null;
        }
        $firstTag = reset($tags);
        $tagId = (int)$firstTag->getId();
        if ($tagId <= 0) {
            return null;
        }

        $sorting = \ZxArt\Shared\SortingParams::fromRequest('votes,desc', PictureListService::ALLOWED_SORT_COLUMNS, 'votes');
        $page = $this->pictureListService->getPagedByLinkedElement($tagId, 'tagLink', $sorting, 0, 8);

        $items = [];
        foreach ($page['items'] as $item) {
            if ($item->id !== $currentId && count($items) < 6) {
                $items[] = $item;
            }
        }
        if (!$items) {
            return null;
        }

        return new PictureRelatedRailDto(
            kind: 'tags',
            title: (string)$this->translationsManager->getTranslationByName('picture.morebytags'),
            kicker: $this->infoBuilder->decodeText((string)$firstTag->getTitle()),
            items: $items,
        );
    }

    /**
     * @param mixed[] $elements
     * @return PictureDto[]
     */
    private function limitPictures(array $elements, int $currentId, int $limit): array
    {
        $items = [];
        foreach ($elements as $candidate) {
            if (!$candidate instanceof zxPictureElement) {
                continue;
            }
            if ($candidate->getId() === $currentId) {
                continue;
            }
            $items[] = $this->picturesTransformer->toDto($candidate);
            if (count($items) >= $limit) {
                break;
            }
        }
        return $items;
    }
}
