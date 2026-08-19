<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use authorAliasElement;
use authorElement;
use fileElement;
use groupAliasElement;
use groupElement;
use structureElement;
use ZxArt\LinkTypes;
use ZxArt\Prods\Dto\ProdSplitDataDto;
use ZxArt\Prods\Dto\ProdSplitGroupDto;
use ZxArt\Prods\Dto\ProdSplitItemDto;
use ZxArt\Prods\ProdElementService;
use ZxArt\Prods\ProdSplitGroup;
use ZxArt\Shared\EntityType;
use ZxArt\Urls\EntityUrlResolver;
use zxProdElement;
use zxReleaseElement;

/**
 * Everything of a production that can be moved to a new production by the split
 * form, grouped the way the `split` action expects the selection back.
 */
readonly class ProdSplitService
{
    /** Thumbnail preset of the screenshots offered for splitting. */
    private const string SCREENSHOT_PRESET = 'prodImage';

    /** Properties whose value is copied to the new production when checked. */
    private const array PROPERTIES = ['title', 'year', 'youtubeId'];

    /** The one link kind that is derived from another link instead of stored on its own. */
    private const string DERIVED_LINK_TYPE = 'wos';

    public function __construct(
        private ProdElementService $prodElementService,
        private EntityUrlResolver $entityUrlResolver,
    ) {
    }

    public function getSplitData(int $prodId): ProdSplitDataDto
    {
        $prod = $this->prodElementService->get($prodId);

        $groups = [];
        foreach (
            [
                ProdSplitGroup::Properties->value => $this->buildProperties($prod),
                ProdSplitGroup::Authors->value => $this->buildAuthors($prod),
                ProdSplitGroup::Publishers->value => $this->buildElements($prod->publishers),
                ProdSplitGroup::Groups->value => $this->buildElements($prod->groups),
                ProdSplitGroup::Releases->value => $this->buildElements($prod->getReleasesList()),
                ProdSplitGroup::Screenshots->value => $this->buildScreenshots($prod),
                ProdSplitGroup::Links->value => $this->buildLinks($prod),
            ] as $group => $items
        ) {
            if ($items !== []) {
                $groups[] = new ProdSplitGroupDto(group: $group, items: $items);
            }
        }

        return new ProdSplitDataDto(
            id: $prod->getId(),
            title: $this->decode((string)$prod->getTitle()),
            groups: $groups,
        );
    }

    /**
     * @return ProdSplitItemDto[]
     */
    private function buildProperties(zxProdElement $prod): array
    {
        $items = [];
        foreach (self::PROPERTIES as $property) {
            /** @var scalar|null $value */
            $value = $prod->$property;
            $title = $this->decode((string)$value);
            if ($title === '' || $title === '0') {
                continue;
            }
            $items[] = new ProdSplitItemDto(
                key: $property,
                title: $title,
                url: null,
                imageUrl: null,
            );
        }

        return $items;
    }

    /**
     * The key of an author item is its authorship record, not the author: the
     * split moves that one credit and leaves the author's other credits alone.
     *
     * @return ProdSplitItemDto[]
     */
    private function buildAuthors(zxProdElement $prod): array
    {
        $items = [];
        /** @var list<array{id: int|string, authorElement: structureElement}> $records */
        $records = $prod->getAuthorsInfo(EntityType::Prod->value);
        foreach ($records as $record) {
            $author = $record['authorElement'];
            if (!$author instanceof authorElement && !$author instanceof authorAliasElement) {
                continue;
            }
            $items[] = new ProdSplitItemDto(
                key: (string)$record['id'],
                title: $this->decode($author->getSearchTitle()),
                url: $this->entityUrlResolver->urlFor($author),
                imageUrl: null,
            );
        }

        return $items;
    }

    /**
     * Publishers and groups are held by an author or a group element, releases by
     * a release element; everything else a legacy list may hold is not offered.
     *
     * @param iterable<mixed> $elements
     * @return ProdSplitItemDto[]
     */
    private function buildElements(iterable $elements): array
    {
        $items = [];
        foreach ($elements as $element) {
            if (
                !$element instanceof authorElement
                && !$element instanceof authorAliasElement
                && !$element instanceof groupElement
                && !$element instanceof groupAliasElement
                && !$element instanceof zxReleaseElement
            ) {
                continue;
            }
            $items[] = new ProdSplitItemDto(
                key: (string)$element->getId(),
                title: $this->decode((string)$element->getSearchTitle()),
                url: $this->entityUrlResolver->urlFor($element),
                imageUrl: null,
            );
        }

        return $items;
    }

    /**
     * @return ProdSplitItemDto[]
     */
    private function buildScreenshots(zxProdElement $prod): array
    {
        $items = [];
        foreach ($prod->getFilesList(LinkTypes::CONNECTED_FILE->value) as $file) {
            if (!$file instanceof fileElement) {
                continue;
            }
            $items[] = new ProdSplitItemDto(
                key: (string)$file->getId(),
                title: $this->decode($file->title !== '' ? $file->title : $file->fileName),
                url: null,
                imageUrl: $file->getImageUrl(self::SCREENSHOT_PRESET),
            );
        }

        return $items;
    }

    /**
     * The key of a link item is its origin and the identifier the production has
     * there, because that pair is what the split moves to the new production.
     *
     * @return ProdSplitItemDto[]
     */
    private function buildLinks(zxProdElement $prod): array
    {
        $items = [];
        /** @var list<array{type: string, name: string, url: string, id: int|string}> $links */
        $links = $prod->getLinksInfo();
        foreach ($links as $link) {
            if ($link['type'] === self::DERIVED_LINK_TYPE) {
                continue;
            }
            $items[] = new ProdSplitItemDto(
                key: $link['type'] . ';' . $link['id'],
                title: $this->decode($link['name']) . ' ' . $link['id'],
                url: $link['url'],
                imageUrl: null,
            );
        }

        return $items;
    }

    private function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
