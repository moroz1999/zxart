<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use DI\Container;
use Search;
use ZxArt\Search\Dto\PhraseSearchHitDto;
use ZxArt\Search\Dto\PhraseSearchQueryDto;
use ZxArt\Search\Dto\PhraseSearchResultDto;
use ZxArt\Shared\Elements\ElementFields;

/**
 * The phrase search every caller runs: the public full and quick searches and
 * the assistant's. Each sets it up for its place through the query; mapping the
 * found elements to its own DTOs stays with the caller.
 *
 * @psalm-api
 */
final readonly class PhraseSearchEngine
{
    /** Alias type => the type of its owner. */
    public const array ALIAS_OWNERS = [
        'authorAlias' => 'author',
        'groupAlias' => 'group',
    ];

    public function __construct(
        private Container $container,
    ) {
    }

    public function find(PhraseSearchQueryDto $query): PhraseSearchResultDto
    {
        $phrase = trim($query->phrase);
        if ($phrase === '' || $query->types === []) {
            return new PhraseSearchResultDto(0, []);
        }
        $isSinglePage = $query->mode === PhraseSearchMode::SinglePage;
        $mergesAliases = $query->aliases === AliasHandling::MergeIntoOwner;

        $search = new Search($this->container);
        // titles and texts are stored HTML-encoded
        $search->setInput(htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8'));
        $search->setTypes($mergesAliases ? $this->withAliases($query->types) : $query->types);
        $search->setLimit($query->limit);
        $search->setPartialMatching(false);
        $search->setContentMatching($query->contentMatching);
        $search->setFilters([]);
        if ($query->languageId !== null) {
            $search->setLanguageId($query->languageId);
        }
        if ($isSinglePage) {
            $search->setSinglePageCombining();
            $search->setRelevanceOrdering();
        } else {
            $search->setOffset($query->offset);
        }
        $result = $search->getResult();

        $hits = [];
        foreach ($result->sets as $set) {
            $type = $set->type;
            $hitType = $mergesAliases ? self::ALIAS_OWNERS[$type] ?? $type : $type;
            foreach ($set->elements as $element) {
                $hits[] = new PhraseSearchHitDto($hitType, $element);
            }
        }
        if ($query->closestFirst) {
            $hits = array_slice($this->closestFirst($hits, $phrase), 0, $query->limit);
        }

        return new PhraseSearchResultDto($isSinglePage ? (int)$result->getSearchTotal() : $result->count, $hits);
    }

    /**
     * @param string[] $types
     * @return string[]
     */
    private function withAliases(array $types): array
    {
        $expanded = [];
        foreach ($types as $type) {
            $expanded[] = $type;
            foreach (self::ALIAS_OWNERS as $aliasType => $ownerType) {
                if ($ownerType === $type) {
                    $expanded[] = $aliasType;
                }
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * Equal to the phrase, then starting with it, then containing it. Each type
     * comes in its own order; a stable sort keeps it among equally close titles.
     *
     * @param PhraseSearchHitDto[] $hits
     * @return PhraseSearchHitDto[]
     */
    private function closestFirst(array $hits, string $phrase): array
    {
        $phrase = mb_strtolower($phrase);
        $closeness = static function (PhraseSearchHitDto $hit) use ($phrase): int {
            $title = mb_strtolower(ElementFields::text($hit->element, 'title'));
            return match (true) {
                $title === $phrase => 0,
                str_starts_with($title, $phrase) => 1,
                str_contains($title, $phrase) => 2,
                default => 3,
            };
        };
        usort($hits, static fn(PhraseSearchHitDto $a, PhraseSearchHitDto $b): int => $closeness($a) <=> $closeness($b));

        return $hits;
    }
}
