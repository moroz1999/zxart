<?php

declare(strict_types=1);

namespace ZxArt\Hardware\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * Data access for the editable hardware catalog.
 *
 * Rows are returned as plain arrays; turning them into DTOs is the service's
 * job. The catalog is ~120 rows, so everything is read in one query and there
 * is no per-code lookup method.
 *
 * @psalm-type HardwareRow = array{id: int, code: string, category: string, position: int}
 * @psalm-type HardwareNameRow = array{hardwareId: int, languageCode: string, name: string, shortName: string}
 */
final readonly class HardwareRepository extends AbstractRepository
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return list<HardwareRow>
     */
    public function getAll(): array
    {
        $rows = $this->db->table($this->tableName(DatabaseTable::Hardware))
            ->orderBy('category')
            ->orderBy('position')
            ->orderBy('id')
            ->get(['id', 'code', 'category', 'position']);

        return array_map(
            static fn(array $row): array => [
                'id' => (int)$row['id'],
                'code' => (string)$row['code'],
                'category' => (string)$row['category'],
                'position' => (int)$row['position'],
            ],
            $rows,
        );
    }

    /**
     * @return list<HardwareNameRow>
     */
    public function getAllNames(): array
    {
        $rows = $this->db->table($this->tableName(DatabaseTable::HardwareName))
            ->get(['hardwareId', 'languageCode', 'name', 'shortName']);

        return array_map(
            static fn(array $row): array => [
                'hardwareId' => (int)$row['hardwareId'],
                'languageCode' => (string)$row['languageCode'],
                'name' => (string)$row['name'],
                'shortName' => (string)$row['shortName'],
            ],
            $rows,
        );
    }

    public function findIdByCode(string $code): ?int
    {
        $id = $this->db->table($this->tableName(DatabaseTable::Hardware))
            ->where('code', '=', $code)
            ->value('id');

        return $id === null ? null : (int)$id;
    }

    public function exists(int $id): bool
    {
        return $this->db->table($this->tableName(DatabaseTable::Hardware))
                ->where('id', '=', $id)
                ->count() > 0;
    }

    public function insert(string $code, string $category, int $position): int
    {
        return (int)$this->db->table($this->tableName(DatabaseTable::Hardware))->insertGetId([
            'code' => $code,
            'category' => $category,
            'position' => $position,
        ]);
    }

    public function update(int $id, string $code, string $category, int $position): void
    {
        $this->db->table($this->tableName(DatabaseTable::Hardware))
            ->where('id', '=', $id)
            ->update([
                'code' => $code,
                'category' => $category,
                'position' => $position,
            ]);
    }

    public function delete(int $id): void
    {
        $this->db->table($this->tableName(DatabaseTable::Hardware))
            ->where('id', '=', $id)
            ->delete();
    }

    /**
     * @param array<string, array{name: string, shortName: string}> $names keyed by language code
     */
    public function replaceNames(int $hardwareId, array $names): void
    {
        $this->db->table($this->tableName(DatabaseTable::HardwareName))
            ->where('hardwareId', '=', $hardwareId)
            ->delete();

        $rows = [];
        foreach ($names as $languageCode => $name) {
            $rows[] = [
                'hardwareId' => $hardwareId,
                'languageCode' => $languageCode,
                'name' => $name['name'],
                'shortName' => $name['shortName'],
            ];
        }
        if ($rows !== []) {
            $this->db->table($this->tableName(DatabaseTable::HardwareName))->insert($rows);
        }
    }

    /**
     * How many links reference this item. Deletion is refused while it is > 0,
     * and the management list shows the number.
     *
     * @return array<int, int> usage count keyed by hardware id
     */
    public function getUsageCounts(): array
    {
        $counts = [];
        foreach ($this->getUsageTables() as $table) {
            $rows = $this->db->table($this->tableName($table))
                ->select('hardwareId')
                ->selectRaw('COUNT(*) AS amount')
                ->groupBy('hardwareId')
                ->get();
            foreach ($rows as $row) {
                $hardwareId = (int)$row['hardwareId'];
                $counts[$hardwareId] = ($counts[$hardwareId] ?? 0) + (int)$row['amount'];
            }
        }

        return $counts;
    }

    /**
     * @return list<DatabaseTable>
     */
    private function getUsageTables(): array
    {
        return [DatabaseTable::ZxReleaseHardware];
    }
}
