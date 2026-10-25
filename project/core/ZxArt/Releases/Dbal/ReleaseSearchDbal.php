<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dbal;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Authors\Dbal\AuthorshipCredits;
use ZxArt\LinkTypes;
use ZxArt\Releases\Dto\ReleaseSearchFilterDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\Dbal\PublishedBy;
use ZxArt\Shared\EntityType;

/**
 * Releases by title, file name and md5, by their production, year, type,
 * formats, hardware and languages, by publishers and credited authors. A
 * release is stored in a row per language, so each comes once.
 *
 * @psalm-api
 */
final readonly class ReleaseSearchDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Ordered by title.
     *
     * @param Builder|null $foundProdIds the productions a nested search found, rows with an `id`
     * @return int[]
     */
    public function findIds(ReleaseSearchFilterDto $filter, ?Builder $foundProdIds, int $start, int $limit): array
    {
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        /** @var int[] $ids */
        $ids = $this->buildQuery($filter, $foundProdIds)
            ->select([$releaseTable . '.id', $releaseTable . '.title'])
            ->distinct()
            ->orderBy($releaseTable . '.title')
            ->orderBy($releaseTable . '.id')
            ->offset($start)
            ->limit($limit)
            ->pluck('id');

        return $ids;
    }

    public function count(ReleaseSearchFilterDto $filter, ?Builder $foundProdIds): int
    {
        return $this->buildQuery($filter, $foundProdIds)
            ->distinct()
            ->count($this->tableName(DatabaseTable::ZxRelease) . '.id');
    }

    private function buildQuery(ReleaseSearchFilterDto $filter, ?Builder $foundProdIds): Builder
    {
        $releaseTable = $this->tableName(DatabaseTable::ZxRelease);
        $idColumn = $releaseTable . '.id';
        $query = $this->db->table($releaseTable);

        $this->applyPart($query, $releaseTable . '.title', $filter->title);
        $this->applyPart($query, $releaseTable . '.fileName', $filter->fileName);
        $md5 = $filter->md5 !== null ? trim($filter->md5) : '';
        if ($md5 !== '') {
            $query->whereIn($idColumn, $this->elementsWith(DatabaseTable::FilesRegistry, 'md5', [$md5]));
        }
        if ($filter->prodIds !== []) {
            $query->whereIn($idColumn, $this->releasesOf($filter->prodIds));
        }
        if ($foundProdIds !== null) {
            $query->whereIn($idColumn, $this->releasesOf((clone $foundProdIds)->select('id')));
        }
        if ($filter->yearFrom !== null || $filter->yearTo !== null) {
            // a year of 0 is unknown and lies in no range
            $query->where($releaseTable . '.year', '>', 0);
            if ($filter->yearFrom !== null) {
                $query->where($releaseTable . '.year', '>=', $filter->yearFrom);
            }
            if ($filter->yearTo !== null) {
                $query->where($releaseTable . '.year', '<=', $filter->yearTo);
            }
        }
        if ($filter->releaseTypes !== []) {
            $query->whereIn($releaseTable . '.releaseType', array_map(static fn(ReleaseTypes $type): string => $type->value, $filter->releaseTypes));
        }
        if ($filter->formats !== []) {
            $query->whereIn($idColumn, $this->elementsWith(DatabaseTable::ZxReleaseFormat, 'value', $filter->formats));
        }
        if ($filter->hardware !== []) {
            $query->whereIn($idColumn, $this->releasesRequiring($filter->hardware));
        }
        if ($filter->languages !== []) {
            $query->whereIn($idColumn, $this->elementsWith(DatabaseTable::ZxItemLanguage, 'value', $filter->languages));
        }
        if ($filter->publisherIds !== []) {
            $query->whereIn($idColumn, PublishedBy::elements(LinkTypes::ZX_RELEASE_PUBLISHERS, $filter->publisherIds));
        }
        AuthorshipCredits::apply($query, $idColumn, EntityType::Release, $filter->authorIds, $filter->anyAuthor, $filter->authorRoles);

        return $query;
    }

    private function applyPart(Builder $query, string $column, ?string $part): void
    {
        $part = $part !== null ? trim($part) : '';
        if ($part === '') {
            return;
        }
        // a backslash escapes the wildcards in MariaDB
        $query->where($column, 'like', '%' . addcslashes($part, '\\%_') . '%');
    }

    /**
     * The elements of a value table holding one of the values.
     *
     * @param string[] $values
     */
    private function elementsWith(DatabaseTable $table, string $column, array $values): Closure
    {
        $tableName = $this->tableName($table);

        return static function (Builder $valueQuery) use ($tableName, $column, $values): void {
            $valueQuery->select('elementId')->from($tableName)->whereIn($column, $values);
        };
    }

    /**
     * @param int[]|Builder $prodIds
     */
    private function releasesOf(array|Builder $prodIds): Closure
    {
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return static function (Builder $linkQuery) use ($linksTable, $prodIds): void {
            $linkQuery->select('childStructureId')
                ->from($linksTable)
                ->where('type', '=', LinkTypes::STRUCTURE->value)
                ->whereIn('parentStructureId', $prodIds);
        };
    }

    /**
     * @param string[] $hardwareCodes
     */
    private function releasesRequiring(array $hardwareCodes): Closure
    {
        $requiredTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        $hardwareTable = $this->tableName(DatabaseTable::Hardware);

        return static function (Builder $requiredQuery) use ($requiredTable, $hardwareTable, $hardwareCodes): void {
            $requiredQuery->select('elementId')
                ->from($requiredTable)
                ->whereIn('hardwareId', static function (Builder $hardwareQuery) use ($hardwareTable, $hardwareCodes): void {
                    $hardwareQuery->select('id')->from($hardwareTable)->whereIn('code', $hardwareCodes);
                });
        };
    }
}
