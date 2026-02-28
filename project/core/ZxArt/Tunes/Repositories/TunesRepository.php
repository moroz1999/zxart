<?php
declare(strict_types=1);


namespace ZxArt\Tunes\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;
use ZxArt\LinkTypes;
use ZxArt\Radio\Dto\RadioCriteriaDto;

readonly final class TunesRepository
{
    public const TABLE = 'module_zxmusic';
    private const string STRUCTURE_LINKS_TABLE = 'structure_links';
    private const string AUTHOR_TABLE = 'module_author';
    private const string VOTES_HISTORY_TABLE = 'votes_history';
    private const string AUTHORSHIP_TYPE = 'authorMusic';

    public function __construct(
        private Connection               $db,
        private AlphanumericColumnSearch $alphanumericColumnSearch,
    )
    {

    }

    public function findTunesByTitle(string $title): array
    {
        $query = $this->getSelectSql();
        $query = $this->alphanumericColumnSearch->addSearchByAlphanumeric($query, $title, 'title');
        $query->orWhere('title', 'like', $title);
        $query->orWhere('title', 'like', $title . '%');
        $query->orWhere('internalTitle', 'like', $title);
        $query->orWhere('internalTitle', 'like', $title . '%');

        return $query->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getNewIds(int $limit): array
    {
        return $this->getSelectSql()
            ->orderBy('dateAdded', 'desc')
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getUnvotedByUserIds(int $userId, int $limit, int $topN): array
    {
        $topIds = $this->getSelectSql()
            ->orderBy('votes', 'desc')
            ->limit($topN)
            ->pluck('id');

        if ($topIds === []) {
            return [];
        }

        return $this->getSelectSql()
            ->whereIn('id', $topIds)
            ->whereNotIn('id', function ($subQuery) use ($userId) {
                $subQuery->select('votes_history.elementId')
                    ->from('votes_history')
                    ->where('votes_history.type', '=', 'zxMusic')
                    ->where('votes_history.userId', '=', $userId);
            })
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');
    }

    /**
     * @return int[]
     */
    public function getRandomGoodIds(int $limit, int $topN): array
    {
        $topIds = $this->getSelectSql()
            ->orderBy('votes', 'desc')
            ->limit($topN)
            ->pluck('id');

        if ($topIds === []) {
            return [];
        }

        return $this->getSelectSql()
            ->whereIn('id', $topIds)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');
    }

    public function findRandomIdByCriteria(RadioCriteriaDto $criteria): ?int
    {
        if ($criteria->bestVotesLimit !== null) {
            $topIds = $this->applyCriteria($this->getSelectSql(), $criteria)
                ->orderBy('votes', 'desc')
                ->limit($criteria->bestVotesLimit)
                ->pluck('id');

            if ($topIds === []) {
                return null;
            }

            $randomIds = $this->applyCriteria($this->getSelectSql(), $criteria)
                ->whereIn('id', $topIds)
                ->inRandomOrder()
                ->limit(1)
                ->pluck('id');
        } else {
            $randomIds = $this->applyCriteria($this->getSelectSql(), $criteria)
                ->inRandomOrder()
                ->limit(1)
                ->pluck('id');
        }

        return $randomIds[0] ?? null;
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table(self::TABLE);
    }

    private function applyCriteria(Builder $query, RadioCriteriaDto $criteria): Builder
    {
        $query->select(self::TABLE . '.id');
        $query->where(self::TABLE . '.mp3Name', '!=', '');

        if ($criteria->minRating !== null) {
            $query->where(self::TABLE . '.votes', '>=', $criteria->minRating);
        }
        if ($criteria->maxRating !== null) {
            $query->where(self::TABLE . '.votes', '<=', $criteria->maxRating);
        }
        if ($criteria->yearsInclude !== []) {
            $query->whereIn(self::TABLE . '.year', $criteria->yearsInclude);
        }
        if ($criteria->yearsExclude !== []) {
            $query->whereNotIn(self::TABLE . '.year', $criteria->yearsExclude);
        }
        if ($criteria->formatGroupsInclude !== []) {
            $query->whereIn(self::TABLE . '.formatGroup', $criteria->formatGroupsInclude);
        }
        if ($criteria->formatGroupsExclude !== []) {
            $query->whereNotIn(self::TABLE . '.formatGroup', $criteria->formatGroupsExclude);
        }
        if ($criteria->formatsInclude !== []) {
            $query->whereIn(self::TABLE . '.type', $criteria->formatsInclude);
        }
        if ($criteria->formatsExclude !== []) {
            $query->whereNotIn(self::TABLE . '.type', $criteria->formatsExclude);
        }
        if ($criteria->minPartyPlace !== null) {
            $query->where(self::TABLE . '.partyplace', '<=', $criteria->minPartyPlace);
            $query->where(self::TABLE . '.partyplace', '!=', 0);
        }
        if ($criteria->requireGame === true) {
            $query->where(self::TABLE . '.game', '!=', 0);
        }
        if ($criteria->hasParty === true) {
            $query->where(self::TABLE . '.partyplace', '>', 0);
        }
        if ($criteria->hasParty === false) {
            $query->where(self::TABLE . '.partyplace', '=', 0);
        }
        if ($criteria->maxPlays !== null) {
            $query->where(self::TABLE . '.plays', '<', $criteria->maxPlays);
        }
        if ($criteria->notVotedByUserId !== null) {
            $query->whereNotIn(
                self::TABLE . '.id',
                function (Builder $subQuery) use ($criteria) {
                    $subQuery->select(self::VOTES_HISTORY_TABLE . '.elementId')
                        ->from(self::VOTES_HISTORY_TABLE)
                        ->where(self::VOTES_HISTORY_TABLE . '.type', '=', 'zxMusic')
                        ->where(self::VOTES_HISTORY_TABLE . '.userId', '=', $criteria->notVotedByUserId);
                }
            );
        }
        if ($criteria->countriesInclude !== [] || $criteria->countriesExclude !== []) {
            $query
                ->join(
                    self::STRUCTURE_LINKS_TABLE . ' as author_links',
                    function ($join) {
                        $join->on('author_links.childStructureId', '=', self::TABLE . '.id')
                            ->where('author_links.type', '=', self::AUTHORSHIP_TYPE);
                    }
                )
                ->join(
                    self::AUTHOR_TABLE,
                    self::AUTHOR_TABLE . '.id',
                    '=',
                    'author_links.parentStructureId'
                )
                ->distinct();

            if ($criteria->countriesInclude !== []) {
                $query->whereIn(self::AUTHOR_TABLE . '.country', $criteria->countriesInclude);
            }
            if ($criteria->countriesExclude !== []) {
                $query->whereNotIn(self::AUTHOR_TABLE . '.country', $criteria->countriesExclude);
            }
        }
        if ($criteria->prodCategoriesInclude !== []) {
            $query
                ->join(
                    self::STRUCTURE_LINKS_TABLE . ' as prod_links',
                    'prod_links.childStructureId',
                    '=',
                    self::TABLE . '.id'
                )
                ->join(
                    self::STRUCTURE_LINKS_TABLE . ' as category_links',
                    'category_links.childStructureId',
                    '=',
                    'prod_links.parentStructureId'
                )
                ->where('prod_links.type', '=', LinkTypes::GAME_LINK->value)
                ->where('category_links.type', '=', LinkTypes::ZX_PROD_CATEGORY->value)
                ->whereIn('category_links.parentStructureId', $criteria->prodCategoriesInclude)
                ->distinct();
        }

        return $query;
    }

    /**
     * Returns IDs of tunes linked to the given author (including all its aliases)
     * via structure_links with type 'authorMusic'.
     * All entity IDs share a single sequence from structure_elements.
     *
     * @return int[]
     */
    public function findIdsByAuthorId(int $authorId): array
    {
        return $this->db->table(self::TABLE)
            ->select(self::TABLE . '.id')
            ->join(
                self::STRUCTURE_LINKS_TABLE,
                fn($join) => $join
                    ->on(self::STRUCTURE_LINKS_TABLE . '.childStructureId', '=', self::TABLE . '.id')
                    ->where(self::STRUCTURE_LINKS_TABLE . '.type', '=', self::AUTHORSHIP_TYPE)
            )
            ->where(function ($q) use ($authorId) {
                $q->where(self::STRUCTURE_LINKS_TABLE . '.parentStructureId', '=', $authorId)
                    ->orWhereIn(
                        self::STRUCTURE_LINKS_TABLE . '.parentStructureId',
                        fn($sub) => $sub->select('id')->from('module_authoralias')->where('authorId', '=', $authorId)
                    );
            })
            ->distinct()
            ->orderBy(self::TABLE . '.title')
            ->pluck(self::TABLE . '.id');
    }

    /**
     * @return array{min: int|null, max: int|null}
     */
    public function getYearRange(): array
    {
        $min = $this->db->table(self::TABLE)
            ->where(self::TABLE . '.year', '>', 0)
            ->min(self::TABLE . '.year');

        $max = $this->db->table(self::TABLE)
            ->where(self::TABLE . '.year', '>', 0)
            ->max(self::TABLE . '.year');

        return [
            'min' => $min === null ? null : (int)$min,
            'max' => $max === null ? null : (int)$max,
        ];
    }

    /**
     * @return array{min: float|null, max: float|null}
     */
    public function getRatingRange(): array
    {
        $min = $this->db->table(self::TABLE)
            ->where(self::TABLE . '.votesAmount', '>', 0)
            ->min(self::TABLE . '.votes');

        $max = $this->db->table(self::TABLE)
            ->where(self::TABLE . '.votesAmount', '>', 0)
            ->max(self::TABLE . '.votes');

        return [
            'min' => $min === null ? null : (float)$min,
            'max' => $max === null ? null : (float)$max,
        ];
    }

    /**
     * @return string[]
     */
    public function getAvailableFormatGroups(): array
    {
        $rows = $this->db->table(self::TABLE)
            ->select(self::TABLE . '.formatGroup as formatGroup')
            ->where(self::TABLE . '.formatGroup', '!=', '')
            ->distinct()
            ->orderBy(self::TABLE . '.formatGroup')
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $value = (string)($row['formatGroup'] ?? '');
            if ($value !== '') {
                $items[] = $value;
            }
        }

        return $items;
    }

    /**
     * @return string[]
     */
    public function getAvailableFormats(): array
    {
        $rows = $this->db->table(self::TABLE)
            ->select(self::TABLE . '.type as type')
            ->where(self::TABLE . '.type', '!=', '')
            ->distinct()
            ->orderBy(self::TABLE . '.type')
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $value = (string)($row['type'] ?? '');
            if ($value !== '') {
                $items[] = $value;
            }
        }

        return $items;
    }

    /**
     * @return int[]
     */
    public function getAuthorCountryIds(): array
    {
        $rows = $this->db->table(self::TABLE)
            ->join(
                self::STRUCTURE_LINKS_TABLE,
                function ($join) {
                    $join->on(self::STRUCTURE_LINKS_TABLE . '.childStructureId', '=', self::TABLE . '.id')
                        ->where(self::STRUCTURE_LINKS_TABLE . '.type', '=', self::AUTHORSHIP_TYPE);
                }
            )
            ->join(
                self::AUTHOR_TABLE,
                self::AUTHOR_TABLE . '.id',
                '=',
                self::STRUCTURE_LINKS_TABLE . '.parentStructureId'
            )
            ->where(self::AUTHOR_TABLE . '.country', '!=', 0)
            ->distinct()
            ->orderBy(self::AUTHOR_TABLE . '.country')
            ->get([self::AUTHOR_TABLE . '.country as country']);

        $items = [];
        foreach ($rows as $row) {
            $value = (int)($row['country'] ?? 0);
            if ($value !== 0) {
                $items[] = $value;
            }
        }

        return $items;
    }
}
