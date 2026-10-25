<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use authorAliasElement;
use authorElement;
use ConfigManager;
use groupAliasElement;
use groupElement;
use partyElement;
use pressArticleElement;
use structureElement;
use ZxArtItem;
use ZxArt\AuthorList\AuthorListTransformer;
use ZxArt\GroupList\GroupListTransformer;
use ZxArt\Parties\PartiesTransformer;
use ZxArt\Pictures\PicturesTransformer;
use ZxArt\Press\Dto\PressArticleDto;
use ZxArt\Prods\ProdsTransformer;
use ZxArt\Search\Dto\PhraseSearchQueryDto;
use ZxArt\Search\Dto\PhraseSearchResultDto;
use ZxArt\Search\Dto\SearchResultPreviewDto;
use ZxArt\Search\Dto\SearchResultsDto;
use ZxArt\Search\Dto\SearchResultSetDto;
use ZxArt\Tunes\TunesTransformer;
use ZxArt\Urls\EntityUrlResolver;
use zxMusicElement;
use zxPictureElement;
use zxProdElement;

readonly class SearchService
{
    public const int DEFAULT_PAGE_SIZE = 50;
    private const int SNIPPET_CUT_LENGTH = 120;

    public function __construct(
        private EntityUrlResolver $entityUrlResolver,
        private PhraseSearchEngine $phraseSearchEngine,
        private ConfigManager $configManager,
        private AuthorListTransformer $authorTransformer,
        private GroupListTransformer $groupTransformer,
        private PicturesTransformer $picturesTransformer,
        private ProdsTransformer $prodsTransformer,
        private TunesTransformer $tunesTransformer,
        private PartiesTransformer $partiesTransformer,
    ) {
    }

    /**
     * @param string[] $requestedTypes public types to include; empty means all configured types
     */
    public function search(string $phrase, int $page, array $requestedTypes): SearchResultsDto
    {
        $page = max(1, $page);
        $pageSize = self::DEFAULT_PAGE_SIZE;
        $result = $this->phraseSearchEngine->find(new PhraseSearchQueryDto(
            phrase: $phrase,
            types: $this->filterPublicTypes($requestedTypes, $this->getAvailableTypes()),
            limit: $pageSize,
            mode: PhraseSearchMode::Paged,
            offset: ($page - 1) * $pageSize,
            contentMatching: true,
            aliases: AliasHandling::MergeIntoOwner,
        ));
        $phrase = trim($phrase);

        return new SearchResultsDto(
            phrase: $phrase,
            page: $page,
            pageSize: $pageSize,
            total: $result->total,
            sets: $this->buildSetDtos(
                $result,
                fn(structureElement $element): ?object => $this->buildItem($element, $phrase),
            ),
        );
    }

    /**
     * @param string[] $requestedTypes public types to include; empty means all configured types
     */
    public function quickSearch(string $phrase, array $requestedTypes, int $pageSize, int $languageId): SearchResultsDto
    {
        $pageSize = min(max(1, $pageSize), 100);
        $result = $this->phraseSearchEngine->find(new PhraseSearchQueryDto(
            phrase: $phrase,
            types: $this->filterPublicTypes($requestedTypes, $this->getAvailableTypes()),
            limit: $pageSize,
            mode: PhraseSearchMode::SinglePage,
            aliases: AliasHandling::MergeIntoOwner,
            languageId: $languageId,
        ));

        return new SearchResultsDto(
            phrase: trim($phrase),
            page: 1,
            pageSize: $pageSize,
            total: $result->total,
            sets: $this->buildSetDtos($result, $this->buildQuickItem(...)),
        );
    }

    /**
     * The configured types, an alias type counted as its owner's: aliases are
     * hidden from the public API.
     *
     * @return string[]
     */
    private function getAvailableTypes(): array
    {
        /** @var string[] $types the structure types listed in `searchtypes-public.php` */
        $types = $this->configManager->getMerged('searchtypes-public.search');
        $public = [];
        foreach ($types as $type) {
            $publicType = PhraseSearchEngine::ALIAS_OWNERS[$type] ?? $type;
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
     * Groups the hits by type, in the order the types first come.
     *
     * @param callable(structureElement): ?object $buildItem
     * @return SearchResultSetDto[]
     */
    private function buildSetDtos(PhraseSearchResultDto $result, callable $buildItem): array
    {
        /** @var array<string, object[]> $itemsByType */
        $itemsByType = [];
        foreach ($result->hits as $hit) {
            $item = $buildItem($hit->element);
            if ($item !== null) {
                $itemsByType[$hit->type][] = $item;
            }
        }

        $dtos = [];
        foreach ($itemsByType as $type => $items) {
            $dtos[] = new SearchResultSetDto(
                type: $type,
                totalCount: count($items),
                items: $items,
            );
        }
        return $dtos;
    }

    private function buildQuickItem(structureElement $element): SearchResultPreviewDto
    {
        return new SearchResultPreviewDto(
            id: (int)$element->id,
            title: $this->getQuickTitle($element),
            url: $this->entityUrlResolver->urlFor($element),
            structureType: (string)$element->structureType,
        );
    }

    private function getQuickTitle(structureElement $element): string
    {
        return match (true) {
            $element instanceof authorElement,
            $element instanceof authorAliasElement,
            $element instanceof groupElement,
            $element instanceof groupAliasElement,
            $element instanceof partyElement,
            $element instanceof pressArticleElement,
            $element instanceof zxProdElement,
            $element instanceof ZxArtItem => html_entity_decode($element->getSearchTitle(), ENT_QUOTES, 'UTF-8'),
            default => html_entity_decode((string)$element->getTitle(), ENT_QUOTES, 'UTF-8'),
        };
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
            $element instanceof pressArticleElement => $this->buildPressArticleDto($element, $phrase),
            $element instanceof partyElement => $this->partiesTransformer->toDto($element),
            default => null,
        };
    }

    private function buildPressArticleDto(pressArticleElement $element, string $phrase): PressArticleDto
    {
        $title = html_entity_decode((string)$element->getTitle(), ENT_QUOTES, 'UTF-8');
        $rawYear = $element->year ?? null;
        $year = ($rawYear === null || $rawYear === '' || $rawYear === false) ? null : (int)$rawYear;
        return new PressArticleDto(
            id: (int)$element->id,
            title: $title,
            titleHtml: $this->highlightTitle($title, $phrase),
            url: $this->entityUrlResolver->urlFor($element),
            snippetHtml: $this->buildSnippet($element, $phrase),
            year: $year,
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
                'url' => $this->entityUrlResolver->urlFor($author),
            ];
        }
        return $result;
    }

}
