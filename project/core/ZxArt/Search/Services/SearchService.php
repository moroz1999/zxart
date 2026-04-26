<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use authorAliasElement;
use authorElement;
use ConfigManager;
use DI\Container;
use groupAliasElement;
use groupElement;
use Search;
use SearchResult;
use SearchResultSet;
use structureElement;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\AuthorList\Dto\AuthorListItemDto;
use ZxArt\GroupList\Dto\GroupListItemDto;
use ZxArt\GroupList\GroupListTransformer;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Search\Dto\SearchItemDto;
use ZxArt\Search\Dto\SearchResultsDto;
use ZxArt\Search\Dto\SearchResultSetDto;
use ZxArt\Tunes\TunesTransformer;
use zxMusicElement;
use zxPictureElement;
use zxProdElement;

readonly class SearchService
{
    public const int DEFAULT_PAGE_SIZE = 50;
    public const int SNIPPET_CUT_LENGTH = 120;

    /**
     * Public type → list of internal types it expands to.
     * Aliases are hidden from the public API and merged into the parent type.
     */
    private const array PUBLIC_TYPE_EXPANSION = [
        'author' => ['author', 'authorAlias'],
        'group' => ['group', 'groupAlias'],
    ];

    public function __construct(
        private Container $container,
        private ConfigManager $configManager,
        private AuthorListTransformer $authorTransformer,
        private GroupListTransformer $groupTransformer,
        private PicturesTransformer $picturesTransformer,
        private ProdsTransformer $prodsTransformer,
        private TunesTransformer $tunesTransformer,
    ) {
    }

    /**
     * @param string[] $requestedTypes public types to include; empty means all configured types
     */
    public function search(string $phrase, int $page, int $pageSize, array $requestedTypes): SearchResultsDto
    {
        $page = max(1, $page);
        $pageSize = max(1, $pageSize);
        $availableTypes = $this->getAvailableTypes();
        $effectivePublicTypes = $this->filterPublicTypes($requestedTypes, $availableTypes);

        $phrase = trim($phrase);
        if ($phrase === '' || $effectivePublicTypes === []) {
            return new SearchResultsDto(
                phrase: $phrase,
                page: $page,
                pageSize: $pageSize,
                total: 0,
                exactMatches: true,
                sets: [],
                availableTypes: $availableTypes,
            );
        }

        $internalTypes = $this->expandToInternalTypes($effectivePublicTypes);
        $offset = ($page - 1) * $pageSize;

        $search = new Search($this->container);
        $search->setInput($phrase);
        $search->setLimit($pageSize);
        $search->setOffset($offset);
        $search->setPartialMatching(true);
        $search->setContentMatching(true);
        $search->setTypes($internalTypes);
        $result = $search->getResult();

        return new SearchResultsDto(
            phrase: $phrase,
            page: $page,
            pageSize: $pageSize,
            total: (int)$result->count,
            exactMatches: (bool)$result->exactMatches,
            sets: $this->buildSetDtos($result, $phrase),
            availableTypes: $availableTypes,
        );
    }

    /**
     * @return string[]
     */
    public function getAvailableTypes(): array
    {
        $types = $this->configManager->getMerged('searchtypes-public.search');
        if (!is_array($types)) {
            return [];
        }
        $internalTypes = array_values(array_unique(array_map('strval', $types)));
        return $this->collapseToPublicTypes($internalTypes);
    }

    /**
     * @param string[] $internalTypes
     * @return string[]
     */
    private function collapseToPublicTypes(array $internalTypes): array
    {
        $aliasToParent = $this->buildAliasToParentMap();
        $public = [];
        foreach ($internalTypes as $type) {
            $publicType = $aliasToParent[$type] ?? $type;
            if (!in_array($publicType, $public, true)) {
                $public[] = $publicType;
            }
        }
        return $public;
    }

    /**
     * @param string[] $requested
     * @param string[] $available
     * @return string[]
     */
    private function filterPublicTypes(array $requested, array $available): array
    {
        if ($requested === []) {
            return $available;
        }
        return array_values(array_intersect($available, $requested));
    }

    /**
     * @param string[] $publicTypes
     * @return string[]
     */
    private function expandToInternalTypes(array $publicTypes): array
    {
        $result = [];
        foreach ($publicTypes as $publicType) {
            $expansion = self::PUBLIC_TYPE_EXPANSION[$publicType] ?? [$publicType];
            foreach ($expansion as $internalType) {
                if (!in_array($internalType, $result, true)) {
                    $result[] = $internalType;
                }
            }
        }
        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function buildAliasToParentMap(): array
    {
        $map = [];
        foreach (self::PUBLIC_TYPE_EXPANSION as $publicType => $internalTypes) {
            foreach ($internalTypes as $internalType) {
                $map[$internalType] = $publicType;
            }
        }
        return $map;
    }

    /**
     * @return SearchResultSetDto[]
     */
    private function buildSetDtos(SearchResult $result, string $phrase): array
    {
        $aliasToParent = $this->buildAliasToParentMap();
        $bucketsOrder = [];
        /** @var array<string, array{type: string, partial: bool, items: object[]}> $buckets */
        $buckets = [];

        foreach ($result->sets as $set) {
            if (!$set instanceof SearchResultSet) {
                continue;
            }
            $publicType = $aliasToParent[(string)$set->type] ?? (string)$set->type;
            if (!isset($buckets[$publicType])) {
                $buckets[$publicType] = [
                    'type' => $publicType,
                    'partial' => (bool)$set->partial,
                    'items' => [],
                ];
                $bucketsOrder[] = $publicType;
            } else {
                $buckets[$publicType]['partial'] = $buckets[$publicType]['partial'] || (bool)$set->partial;
            }
            foreach ($set->elements as $element) {
                if (!$element instanceof structureElement) {
                    continue;
                }
                $item = $this->buildItem($element, $phrase);
                if ($item !== null) {
                    $buckets[$publicType]['items'][] = $item;
                }
            }
        }

        $dtos = [];
        foreach ($bucketsOrder as $publicType) {
            $bucket = $buckets[$publicType];
            if ($bucket['items'] === []) {
                continue;
            }
            $items = $this->sortBucketItems($publicType, $bucket['items']);
            $dtos[] = new SearchResultSetDto(
                type: $bucket['type'],
                partial: $bucket['partial'],
                totalCount: count($items),
                items: $items,
            );
        }
        return $dtos;
    }

    /**
     * @param object[] $items
     * @return object[]
     */
    private function sortBucketItems(string $publicType, array $items): array
    {
        if ($publicType === 'author') {
            usort($items, function ($a, $b): int {
                $primary = $this->compareCi($this->authorTitle($a), $this->authorTitle($b));
                if ($primary !== 0) {
                    return $primary;
                }
                return $this->compareCi($this->authorRealName($a), $this->authorRealName($b));
            });
            return $items;
        }
        if ($publicType === 'group') {
            usort($items, function ($a, $b): int {
                $primary = $this->compareCi($this->groupTitle($a), $this->groupTitle($b));
                if ($primary !== 0) {
                    return $primary;
                }
                return $this->compareCi($this->groupRealTitle($a), $this->groupRealTitle($b));
            });
            return $items;
        }
        return $items;
    }

    private function compareCi(string $a, string $b): int
    {
        return strnatcasecmp($a, $b);
    }

    private function authorTitle(object $item): string
    {
        return $item instanceof AuthorListItemDto ? $item->title : '';
    }

    private function authorRealName(object $item): string
    {
        return $item instanceof AuthorListItemDto ? $item->realName : '';
    }

    private function groupTitle(object $item): string
    {
        return $item instanceof GroupListItemDto ? $item->title : '';
    }

    private function groupRealTitle(object $item): string
    {
        return $item instanceof GroupListItemDto ? ($item->realGroupTitle ?? '') : '';
    }

    private function buildItem(structureElement $element, string $phrase): ?object
    {
        return match (true) {
            $element instanceof authorElement => $this->authorTransformer->authorToDto($element),
            $element instanceof authorAliasElement => $this->authorTransformer->aliasToDto($element),
            $element instanceof groupElement => $this->groupTransformer->groupToDto($element),
            $element instanceof groupAliasElement => $this->groupTransformer->aliasToDto($element),
            $element instanceof zxPictureElement => $this->picturesTransformer->toDto($element),
            $element instanceof zxProdElement => $this->prodsTransformer->toDto($element),
            $element instanceof zxMusicElement => $this->tunesTransformer->toDto($element),
            default => $this->buildGenericItem($element, $phrase),
        };
    }

    private function buildGenericItem(structureElement $element, string $phrase): SearchItemDto
    {
        $title = html_entity_decode((string)$element->getTitle(), ENT_QUOTES, 'UTF-8');
        return new SearchItemDto(
            id: (int)$element->id,
            type: (string)$element->structureType,
            title: $title,
            titleHtml: $this->highlightTitle($title, $phrase),
            url: $element->getUrl(),
            snippetHtml: $this->buildSnippet($element, $phrase),
            year: $this->readIntOrNull($element, 'year'),
            authors: $this->collectAuthors($element),
        );
    }

    private function buildSnippet(structureElement $element, string $phrase): ?string
    {
        $sources = ['introduction', 'content', 'description'];
        $candidates = [];
        foreach ($sources as $field) {
            $raw = $element->$field ?? null;
            if (!is_string($raw) || $raw === '') {
                continue;
            }
            $text = trim(html_entity_decode(strip_tags($raw), ENT_QUOTES, 'UTF-8'));
            if ($text === '') {
                continue;
            }
            $candidates[] = $text;
        }
        if ($candidates === []) {
            return null;
        }
        if ($phrase !== '') {
            foreach ($candidates as $text) {
                if (mb_stripos($text, $phrase) !== false) {
                    return $this->highlightSnippet($text, $phrase);
                }
            }
        }
        return $this->highlightSnippet($candidates[0], $phrase);
    }

    private function highlightTitle(string $title, string $phrase): string
    {
        $escaped = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return $this->wrapPhrase($escaped, $phrase);
    }

    private function highlightSnippet(string $text, string $phrase): string
    {
        $phraseLength = mb_strlen($phrase);
        $position = $phrase !== '' ? mb_stripos($text, $phrase) : false;
        $textLength = mb_strlen($text);

        if ($position === false) {
            $cut = mb_substr($text, 0, self::SNIPPET_CUT_LENGTH * 2);
            $snippet = $textLength > self::SNIPPET_CUT_LENGTH * 2 ? $cut . '...' : $cut;
            return htmlspecialchars($snippet, ENT_QUOTES, 'UTF-8');
        }

        $start = $position > self::SNIPPET_CUT_LENGTH ? $position - self::SNIPPET_CUT_LENGTH : 0;
        $end = min($textLength, $position + $phraseLength + self::SNIPPET_CUT_LENGTH);
        $snippet = mb_substr($text, $start, $end - $start);
        if ($start > 0) {
            $snippet = '...' . $snippet;
        }
        if ($end < $textLength) {
            $snippet .= '...';
        }
        $escaped = htmlspecialchars($snippet, ENT_QUOTES, 'UTF-8');
        return $this->wrapPhrase($escaped, $phrase);
    }

    private function wrapPhrase(string $escapedText, string $phrase): string
    {
        if ($phrase === '') {
            return $escapedText;
        }
        $escapedPhrase = htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8');
        $pattern = '/' . preg_quote($escapedPhrase, '/') . '/iu';
        $replacement = '<span class="search_emphasized">$0</span>';
        $result = preg_replace($pattern, $replacement, $escapedText);
        return $result ?? $escapedText;
    }

    /**
     * @return array<array{title: string, url: string}>
     */
    private function collectAuthors(structureElement $element): array
    {
        if (!method_exists($element, 'getAuthorsList')) {
            return [];
        }
        $authorsList = $element->getAuthorsList();
        if (!is_array($authorsList)) {
            return [];
        }
        $result = [];
        foreach ($authorsList as $author) {
            if (!$author instanceof structureElement) {
                continue;
            }
            $result[] = [
                'title' => html_entity_decode((string)$author->getTitle(), ENT_QUOTES, 'UTF-8'),
                'url' => $author->getUrl(),
            ];
        }
        return $result;
    }

    private function readIntOrNull(structureElement $element, string $field): ?int
    {
        $value = $element->$field ?? null;
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        return (int)$value;
    }
}
