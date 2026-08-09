<?php

declare(strict_types=1);

namespace ZxArt\Prods\Repositories;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * Reads hardware across the prod/release boundary.
 *
 * Hardware lives on both sides: the shared set on the production, the specific
 * deviations on each release. Almost every question about it therefore spans the
 * two tables, which is what this repository is for.
 */
final readonly class ProdHardwareRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * Codes on the production itself plus those on any of its releases — what a
     * catalogue card or a search index should show.
     *
     * @return list<string>
     */
    public function getAggregatedCodes(int $prodId): array
    {
        $codes = $this->db->table($this->tableName(DatabaseTable::Hardware))
            ->whereIn('id', $this->prodOwnHardwareIds($prodId))
            ->orWhereIn('id', $this->releaseHardwareIds($prodId))
            ->distinct()
            ->pluck('code');

        return array_map(static fn(mixed $code): string => (string)$code, $codes);
    }

    /**
     * Codes on the production of the given release — what the release inherits.
     *
     * @return list<string>
     */
    public function getProdCodesForRelease(int $releaseId): array
    {
        $codes = $this->db->table($this->tableName(DatabaseTable::Hardware))
            ->whereIn('id', function (Builder $query) use ($releaseId): void {
                $query->from($this->tableName(DatabaseTable::ZxProdHardware))
                    ->select('hardwareId')
                    ->whereIn('elementId', function (Builder $parents) use ($releaseId): void {
                        $parents->from($this->tableName(DatabaseTable::StructureLinks))
                            ->select('parentStructureId')
                            ->where('childStructureId', '=', $releaseId)
                            ->where('type', '=', LinkTypes::STRUCTURE->value);
                    });
            })
            ->distinct()
            ->pluck('code');

        return array_map(static fn(mixed $code): string => (string)$code, $codes);
    }

    /**
     * Production ids carrying any of the given hardware, on the prod itself or on
     * one of its releases. Ids rather than codes: the caller resolves codes once
     * through the cached catalog, so the query compares integers.
     *
     * @param list<int> $hardwareIds
     */
    public function addProdHardwareFilter(Builder $query, string $prodIdColumn, array $hardwareIds): Builder
    {
        $prodHardwareTable = $this->tableName(DatabaseTable::ZxProdHardware);
        $releaseHardwareTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return $query->where(function (Builder $matching) use (
            $prodIdColumn,
            $hardwareIds,
            $prodHardwareTable,
            $releaseHardwareTable,
            $linksTable
        ): void {
            $matching->whereIn($prodIdColumn, function (Builder $own) use ($prodHardwareTable, $hardwareIds): void {
                $own->from($prodHardwareTable)->select('elementId')->whereIn('hardwareId', $hardwareIds);
            })->orWhereIn($prodIdColumn, function (Builder $viaRelease) use (
                $linksTable,
                $releaseHardwareTable,
                $hardwareIds
            ): void {
                $viaRelease->from($linksTable)
                    ->select('parentStructureId')
                    ->where('type', '=', LinkTypes::STRUCTURE->value)
                    ->whereIn('childStructureId', function (Builder $releases) use ($releaseHardwareTable, $hardwareIds): void {
                        $releases->from($releaseHardwareTable)->select('elementId')->whereIn('hardwareId', $hardwareIds);
                    });
            });
        });
    }

    /**
     * Release ids carrying any of the given hardware themselves, or inheriting it
     * from their production.
     *
     * @param list<int> $hardwareIds
     */
    public function addReleaseHardwareFilter(Builder $query, string $releaseIdColumn, array $hardwareIds): Builder
    {
        $prodHardwareTable = $this->tableName(DatabaseTable::ZxProdHardware);
        $releaseHardwareTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return $query->where(function (Builder $matching) use (
            $releaseIdColumn,
            $hardwareIds,
            $prodHardwareTable,
            $releaseHardwareTable,
            $linksTable
        ): void {
            $matching->whereIn($releaseIdColumn, function (Builder $own) use ($releaseHardwareTable, $hardwareIds): void {
                $own->from($releaseHardwareTable)->select('elementId')->whereIn('hardwareId', $hardwareIds);
            })->orWhereIn($releaseIdColumn, function (Builder $viaProd) use (
                $linksTable,
                $prodHardwareTable,
                $hardwareIds
            ): void {
                $viaProd->from($linksTable)
                    ->select('childStructureId')
                    ->where('type', '=', LinkTypes::STRUCTURE->value)
                    ->whereIn('parentStructureId', function (Builder $prods) use ($prodHardwareTable, $hardwareIds): void {
                        $prods->from($prodHardwareTable)->select('elementId')->whereIn('hardwareId', $hardwareIds);
                    });
            });
        });
    }

    /**
     * Hardware ids present anywhere in the given set of productions — on them or
     * on their releases. Feeds the catalogue's hardware selector.
     *
     * @return list<int>
     */
    public function getHardwareIdsForProds(Builder $prodIdsQuery): array
    {
        $prodHardwareTable = $this->tableName(DatabaseTable::ZxProdHardware);
        $releaseHardwareTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        $ownIds = $this->db->table($prodHardwareTable)
            ->whereIn('elementId', $prodIdsQuery)
            ->distinct()
            ->pluck('hardwareId');

        $releaseIds = $this->db->table($releaseHardwareTable)
            ->whereIn('elementId', function (Builder $releases) use ($linksTable, $prodIdsQuery): void {
                $releases->from($linksTable)
                    ->select('childStructureId')
                    ->where('type', '=', LinkTypes::STRUCTURE->value)
                    ->whereIn('parentStructureId', $prodIdsQuery);
            })
            ->distinct()
            ->pluck('hardwareId');

        $ids = array_map(
            static fn(mixed $id): int => (int)$id,
            [...$ownIds, ...$releaseIds],
        );

        return array_values(array_unique($ids));
    }

    private function prodOwnHardwareIds(int $prodId): callable
    {
        $table = $this->tableName(DatabaseTable::ZxProdHardware);

        return static function (Builder $query) use ($table, $prodId): void {
            $query->from($table)->select('hardwareId')->where('elementId', '=', $prodId);
        };
    }

    private function releaseHardwareIds(int $prodId): callable
    {
        $releaseHardwareTable = $this->tableName(DatabaseTable::ZxReleaseHardware);
        $linksTable = $this->tableName(DatabaseTable::StructureLinks);

        return static function (Builder $query) use ($releaseHardwareTable, $linksTable, $prodId): void {
            $query->from($releaseHardwareTable)
                ->select('hardwareId')
                ->whereIn('elementId', function (Builder $releases) use ($linksTable, $prodId): void {
                    $releases->from($linksTable)
                        ->select('childStructureId')
                        ->where('parentStructureId', '=', $prodId)
                        ->where('type', '=', LinkTypes::STRUCTURE->value);
                });
        };
    }
}
