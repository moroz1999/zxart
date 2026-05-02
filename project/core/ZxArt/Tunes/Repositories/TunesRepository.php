<?php
declare(strict_types=1);


namespace ZxArt\Tunes\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Helpers\AlphanumericColumnSearch;
use ZxArt\LinkTypes;
use ZxArt\Radio\Dto\RadioCriteriaDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;
use ZxArt\Shared\SortingParams;
use ZxArt\Shared\StructureType;

readonly final class TunesRepository extends AbstractRepository
{
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

        /** @var int[] $ids */
        $ids = $query->pluck('id');

        return $ids;
    }

    /**
     * @return int[]
     */
    public function getNewIds(int $limit): array
    {
        /** @var int[] $ids */
        $ids = $this->getSelectSql()
            ->orderBy('dateAdded', 'desc')
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    /**
     * @return int[]
     */
    public function getUnvotedByUserIds(int $userId, int $limit, int $topN): array
    {
        /** @var int[] $topIds */
        $topIds = $this->getSelectSql()
            ->where('denyVoting', '=', 0)
            ->orderBy('votes', 'desc')
            ->limit($topN)
            ->pluck('id');

        if ($topIds === []) {
            return [];
        }

        $votedIdsQuery = $this->getUserVotesQuery($userId);
        /** @var int[] $ids */
        $ids = $this->getSelectSql()
            ->whereIn('id', $topIds)
            ->whereNotIn('id', $votedIdsQuery)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    /**
     * @return int[]
     */
    public function getRandomGoodIds(int $limit, int $topN): array
    {
        /** @var int[] $topIds */
        $topIds = $this->getSelectSql()
            ->orderBy('votes', 'desc')
            ->limit($topN)
            ->pluck('id');

        if ($topIds === []) {
            return [];
        }

        /** @var int[] $ids */
        $ids = $this->getSelectSql()
            ->whereIn('id', $topIds)
            ->inRandomOrder()
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function findRandomIdByCriteria(RadioCriteriaDto $criteria): ?int
    {
        if ($criteria->bestVotesLimit !== null) {
            /** @var int[] $topIds */
            $topIds = $this->applyCriteria($this->getSelectSql(), $criteria)
                ->orderBy('votes', 'desc')
                ->limit($criteria->bestVotesLimit)
                ->pluck('id');

            if ($topIds === []) {
                return null;
            }

            /** @var int[] $randomIds */
            $randomIds = $this->applyCriteria($this->getSelectSql(), $criteria)
                ->whereIn('id', $topIds)
                ->inRandomOrder()
                ->limit(1)
                ->pluck('id');
        } else {
            /** @var int[] $randomIds */
            $randomIds = $this->applyCriteria($this->getSelectSql(), $criteria)
                ->inRandomOrder()
                ->limit(1)
                ->pluck('id');
        }

        return $randomIds[0] ?? null;
    }

    /**
     * @return int[]
     */
    public function findPagedByLinkedElement(
        int $elementId,
        string $linkType,
        SortingParams $sorting,
        int $start,
        int $limit,
    ): array {
        /** @var int[] $ids */
        $ids = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->join(
                $this->tableName(DatabaseTable::StructureLinks),
                $this->tableColumn(DatabaseTable::StructureLinks, 'childStructureId'),
                '=',
                $this->tableColumn(DatabaseTable::ZxMusic, 'id')
            )
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'type'), '=', $linkType)
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId'), '=', $elementId)
            ->orderBy($this->tableColumn(DatabaseTable::ZxMusic, $sorting->column), $sorting->direction->value)
            ->offset($start)
            ->limit($limit)
            ->pluck($this->tableColumn(DatabaseTable::ZxMusic, 'id'));

        return $ids;
    }

    public function countByLinkedElement(int $elementId, string $linkType): int
    {
        return $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->join(
                $this->tableName(DatabaseTable::StructureLinks),
                $this->tableColumn(DatabaseTable::StructureLinks, 'childStructureId'),
                '=',
                $this->tableColumn(DatabaseTable::ZxMusic, 'id')
            )
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'type'), '=', $linkType)
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId'), '=', $elementId)
            ->count();
    }

    private function getSelectSql(): Builder
    {
        return $this->db->table($this->tableName(DatabaseTable::ZxMusic));
    }

    private function applyCriteria(Builder $query, RadioCriteriaDto $criteria): Builder
    {
        $query->select($this->tableColumn(DatabaseTable::ZxMusic, 'id'));
        $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'mp3Name'), '!=', '');

        if ($criteria->minRating !== null) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'votes'), '>=', $criteria->minRating);
        }
        if ($criteria->maxRating !== null) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'votes'), '<=', $criteria->maxRating);
        }
        if ($criteria->yearsInclude !== []) {
            $query->whereIn($this->tableColumn(DatabaseTable::ZxMusic, 'year'), $criteria->yearsInclude);
        }
        if ($criteria->yearsExclude !== []) {
            $query->whereNotIn($this->tableColumn(DatabaseTable::ZxMusic, 'year'), $criteria->yearsExclude);
        }
        if ($criteria->formatGroupsInclude !== []) {
            $query->whereIn($this->tableColumn(DatabaseTable::ZxMusic, 'formatGroup'), $criteria->formatGroupsInclude);
        }
        if ($criteria->formatGroupsExclude !== []) {
            $query->whereNotIn($this->tableColumn(DatabaseTable::ZxMusic, 'formatGroup'), $criteria->formatGroupsExclude);
        }
        if ($criteria->formatsInclude !== []) {
            $query->whereIn($this->tableColumn(DatabaseTable::ZxMusic, 'type'), $criteria->formatsInclude);
        }
        if ($criteria->formatsExclude !== []) {
            $query->whereNotIn($this->tableColumn(DatabaseTable::ZxMusic, 'type'), $criteria->formatsExclude);
        }
        if ($criteria->minPartyPlace !== null) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'partyplace'), '<=', $criteria->minPartyPlace);
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'partyplace'), '!=', 0);
        }
        if ($criteria->requireGame === true) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'game'), '!=', 0);
        }
        if ($criteria->hasParty === true) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'partyplace'), '>', 0);
        }
        if ($criteria->hasParty === false) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'partyplace'), '=', 0);
        }
        if ($criteria->maxPlays !== null) {
            $query->where($this->tableColumn(DatabaseTable::ZxMusic, 'plays'), '<', $criteria->maxPlays);
        }
        if ($criteria->notVotedByUserId !== null) {
            $query->whereNotIn($this->tableColumn(DatabaseTable::ZxMusic, 'id'), $this->getUserVotesQuery($criteria->notVotedByUserId));
        }
        if ($criteria->countriesInclude !== [] || $criteria->countriesExclude !== []) {
            $query
                ->join(
                    $this->tableAlias(DatabaseTable::StructureLinks, 'author_links'),
                    'author_links.childStructureId',
                    '=',
                    $this->tableColumn(DatabaseTable::ZxMusic, 'id')
                )
                ->where('author_links.type', '=', LinkTypes::AUTHOR_MUSIC->value)
                ->join(
                    $this->tableName(DatabaseTable::Author),
                    $this->tableColumn(DatabaseTable::Author, 'id'),
                    '=',
                    'author_links.parentStructureId'
                )
                ->distinct();

            if ($criteria->countriesInclude !== []) {
                $query->whereIn($this->tableColumn(DatabaseTable::Author, 'country'), $criteria->countriesInclude);
            }
            if ($criteria->countriesExclude !== []) {
                $query->whereNotIn($this->tableColumn(DatabaseTable::Author, 'country'), $criteria->countriesExclude);
            }
        }
        if ($criteria->prodCategoriesInclude !== []) {
            $query
                ->join(
                    $this->tableAlias(DatabaseTable::StructureLinks, 'prod_links'),
                    'prod_links.childStructureId',
                    '=',
                    $this->tableColumn(DatabaseTable::ZxMusic, 'id')
                )
                ->join(
                    $this->tableAlias(DatabaseTable::StructureLinks, 'category_links'),
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
     * via authorship links.
     * All entity IDs share a single sequence from structure_elements.
     *
     * @return int[]
     */
    public function findIdsByAuthorId(int $authorId): array
    {
        $authorIds = $this->getAuthorAndAliasIds($authorId);

        /** @var int[] $ids */
        $ids = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->select($this->tableColumn(DatabaseTable::ZxMusic, 'id'))
            ->join(
                $this->tableName(DatabaseTable::StructureLinks),
                $this->tableColumn(DatabaseTable::StructureLinks, 'childStructureId'),
                '=',
                $this->tableColumn(DatabaseTable::ZxMusic, 'id')
            )
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'type'), '=', LinkTypes::AUTHOR_MUSIC->value)
            ->whereIn($this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId'), $authorIds)
            ->distinct()
            ->orderBy($this->tableColumn(DatabaseTable::ZxMusic, 'title'))
            ->pluck($this->tableColumn(DatabaseTable::ZxMusic, 'id'));

        return $ids;
    }


    /**
     * @return array{min: int|null, max: int|null}
     */
    public function getYearRange(): array
    {
        /** @var int|null $min */
        $min = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->where($this->tableColumn(DatabaseTable::ZxMusic, 'year'), '>', 0)
            ->min($this->tableColumn(DatabaseTable::ZxMusic, 'year'));

        /** @var int|null $max */
        $max = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->where($this->tableColumn(DatabaseTable::ZxMusic, 'year'), '>', 0)
            ->max($this->tableColumn(DatabaseTable::ZxMusic, 'year'));

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * @return array{min: float|null, max: float|null}
     */
    public function getRatingRange(): array
    {
        /** @var float|null $min */
        $min = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->where($this->tableColumn(DatabaseTable::ZxMusic, 'votesAmount'), '>', 0)
            ->min($this->tableColumn(DatabaseTable::ZxMusic, 'votes'));

        /** @var float|null $max */
        $max = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->where($this->tableColumn(DatabaseTable::ZxMusic, 'votesAmount'), '>', 0)
            ->max($this->tableColumn(DatabaseTable::ZxMusic, 'votes'));

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * @return string[]
     */
    public function getAvailableFormatGroups(): array
    {
        /** @var string[] $formatGroups */
        $formatGroups = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->where($this->tableColumn(DatabaseTable::ZxMusic, 'formatGroup'), '!=', '')
            ->distinct()
            ->orderBy($this->tableColumn(DatabaseTable::ZxMusic, 'formatGroup'))
            ->pluck($this->tableColumn(DatabaseTable::ZxMusic, 'formatGroup'));

        return $formatGroups;
    }

    /**
     * @return string[]
     */
    public function getAvailableFormats(): array
    {
        /** @var string[] $formats */
        $formats = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->where($this->tableColumn(DatabaseTable::ZxMusic, 'type'), '!=', '')
            ->distinct()
            ->orderBy($this->tableColumn(DatabaseTable::ZxMusic, 'type'))
            ->pluck($this->tableColumn(DatabaseTable::ZxMusic, 'type'));

        return $formats;
    }

    /**
     * @return int[]
     */
    public function getAuthorCountryIds(): array
    {
        /** @var int[] $countryIds */
        $countryIds = $this->db->table($this->tableName(DatabaseTable::ZxMusic))
            ->join(
                $this->tableName(DatabaseTable::StructureLinks),
                $this->tableColumn(DatabaseTable::StructureLinks, 'childStructureId'),
                '=',
                $this->tableColumn(DatabaseTable::ZxMusic, 'id')
            )
            ->where($this->tableColumn(DatabaseTable::StructureLinks, 'type'), '=', LinkTypes::AUTHOR_MUSIC->value)
            ->join(
                $this->tableName(DatabaseTable::Author),
                $this->tableColumn(DatabaseTable::Author, 'id'),
                '=',
                $this->tableColumn(DatabaseTable::StructureLinks, 'parentStructureId')
            )
            ->where($this->tableColumn(DatabaseTable::Author, 'country'), '!=', 0)
            ->distinct()
            ->orderBy($this->tableColumn(DatabaseTable::Author, 'country'))
            ->pluck($this->tableColumn(DatabaseTable::Author, 'country'));

        return $countryIds;
    }

    private function getUserVotesQuery(int $userId): Builder
    {
        return $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->select($this->tableColumn(DatabaseTable::VotesHistory, 'elementId'))
            ->where($this->tableColumn(DatabaseTable::VotesHistory, 'type'), '=', StructureType::ZxMusic->value)
            ->where($this->tableColumn(DatabaseTable::VotesHistory, 'userId'), '=', $userId);
    }

    /**
     * @return int[]
     */
    private function getAuthorAndAliasIds(int $authorId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::AuthorAlias))
            ->where('authorId', '=', $authorId)
            ->pluck('id');

        return [$authorId, ...$aliasIds];
    }
}
