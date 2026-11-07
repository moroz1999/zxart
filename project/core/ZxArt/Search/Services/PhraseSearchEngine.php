<?php

declare(strict_types=1);

namespace ZxArt\Search\Services;

use structureElement;
use structureManager;
use ZxArt\Search\Dbal\PhraseSearchDbal;
use ZxArt\Search\Dbal\TitleMatch;
use ZxArt\Search\Dto\PhraseSearchHitDto;
use ZxArt\Search\Dto\PhraseSearchQueryDto;
use ZxArt\Search\Dto\PhraseSearchResultDto;
use ZxArt\Search\Dto\TypeCountDto;
use ZxArt\Search\Dto\TypeSliceDto;
use ZxArt\Shared\Elements\ElementFields;

/**
 * The phrase search every caller runs: the public full and quick searches,
 * the relation pickers and the assistant's. Each sets it up for its place
 * through the query; mapping the found elements to its own DTOs stays with
 * the caller.
 *
 * @psalm-api
 */
final readonly class PhraseSearchEngine
{
    public function __construct(
        private PhraseSearchDbal $phraseSearchDbal,
        private TitleMatch $titleMatch,
        private SinglePageQuota $singlePageQuota,
        private PageWindow $pageWindow,
        private structureManager $structureManager,
    ) {
    }

    public function find(PhraseSearchQueryDto $query): PhraseSearchResultDto
    {
        $phrase = trim($query->phrase);
        if ($this->titleMatch->key($phrase) === '' || $query->types === []) {
            return new PhraseSearchResultDto(0, []);
        }
        $isSinglePage = $query->mode === PhraseSearchMode::SinglePage;
        $mergesAliases = $query->aliases === AliasHandling::MergeIntoOwner;
        $types = $this->distinct($mergesAliases ? $this->withAliases($query->types) : $query->types);

        /** @var array<string, int[]> $idsByType */
        $idsByType = [];
        $counts = [];
        foreach ($types as $type) {
            $ids = $this->phraseSearchDbal->ids($type, $phrase, $query->contentMatching, $isSinglePage, $query->languageId);
            $idsByType[$type->value] = $ids;
            $counts[] = new TypeCountDto($type, count($ids));
        }
        $slices = $isSinglePage
            ? $this->singlePageQuota->slices($query->limit, $counts)
            : $this->pageWindow->slices($query->offset, $query->limit, $counts);

        $hits = [];
        $missing = 0;
        foreach ($slices as $slice) {
            foreach (array_slice($idsByType[$slice->type->value], $slice->offset, $slice->limit) as $id) {
                $element = $this->loadElement($id, $query->languageId);
                if ($element === null) {
                    $missing++;
                    continue;
                }
                $hitType = $mergesAliases ? $slice->type->aliasOwner() ?? $slice->type : $slice->type;
                $hits[] = new PhraseSearchHitDto($hitType, $element);
            }
        }
        if ($query->closestFirst) {
            $hits = array_slice($this->closestFirst($hits, $phrase), 0, $query->limit);
        }
        $total = array_sum(array_map(static fn(TypeCountDto $count): int => $count->count, $counts));

        // an element the visitor may not see is left out of the single page's total as well
        return new PhraseSearchResultDto($isSinglePage ? $total - $missing : $total, $hits);
    }

    private function loadElement(int $id, ?int $languageId): ?structureElement
    {
        // the language root is the parent the element is looked for under
        return $this->structureManager->getElementById($id, $languageId);
    }

    /**
     * Each type followed by its alias, when it has one.
     *
     * @param PhraseSearchType[] $types
     * @return PhraseSearchType[]
     */
    private function withAliases(array $types): array
    {
        $expanded = [];
        foreach ($types as $type) {
            $expanded[] = $type;
            foreach (PhraseSearchType::cases() as $candidate) {
                if ($candidate->aliasOwner() === $type) {
                    $expanded[] = $candidate;
                }
            }
        }

        return $expanded;
    }

    /**
     * @param PhraseSearchType[] $types
     * @return list<PhraseSearchType>
     */
    private function distinct(array $types): array
    {
        $distinct = [];
        foreach ($types as $type) {
            $distinct[$type->value] = $type;
        }

        return array_values($distinct);
    }

    /**
     * The title's key equal to the phrase's, then starting with it, then the
     * rest. Each type comes in its own order; a stable sort keeps it among
     * equally close titles.
     *
     * @param PhraseSearchHitDto[] $hits
     * @return PhraseSearchHitDto[]
     */
    private function closestFirst(array $hits, string $phrase): array
    {
        $phraseKey = $this->titleMatch->key($phrase);
        $closeness = function (PhraseSearchHitDto $hit) use ($phraseKey): int {
            $titleKey = $this->titleMatch->key(ElementFields::text($hit->element, 'title'));
            return match (true) {
                $titleKey === $phraseKey => 0,
                str_starts_with($titleKey, $phraseKey) => 1,
                default => 2,
            };
        };
        usort($hits, static fn(PhraseSearchHitDto $a, PhraseSearchHitDto $b): int => $closeness($a) <=> $closeness($b));

        return $hits;
    }
}
