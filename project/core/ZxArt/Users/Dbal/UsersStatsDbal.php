<?php

declare(strict_types=1);

namespace ZxArt\Users\Dbal;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\StructureType;

/**
 * The figures of the users dashboard.
 */
final readonly class UsersStatsDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    public function countAll(): int
    {
        return $this->users()->count();
    }

    public function countRegisteredSince(int $timestamp): int
    {
        return $this->users()
            ->where($this->tableColumn(DatabaseTable::StructureElements, 'dateCreated'), '>=', $timestamp)
            ->count();
    }

    public function countBanned(): int
    {
        return $this->users()->where($this->tableColumn(DatabaseTable::User, 'banned'), '=', 1)->count();
    }

    public function countUnverified(): int
    {
        return $this->users()->where($this->tableColumn(DatabaseTable::User, 'verified'), '=', 0)->count();
    }

    /**
     * @return array<string, int> Y-m => registrations, for the months that have any
     */
    public function countRegistrationsByMonthSince(int $timestamp): array
    {
        $elements = $this->tableName(DatabaseTable::StructureElements);
        /** @var list<array{month: string, amount: int|string}> $rows */
        $rows = $this->db->table($elements)
            ->where('structureType', '=', StructureType::User->value)
            ->where('dateCreated', '>=', $timestamp)
            ->selectRaw("DATE_FORMAT(FROM_UNIXTIME(dateCreated), '%Y-%m') AS month")
            ->selectRaw('COUNT(*) AS amount')
            ->groupBy('month')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['month']] = (int)$row['amount'];
        }

        return $counts;
    }

    private function users(): Builder
    {
        $users = $this->tableName(DatabaseTable::User);
        $elements = $this->tableName(DatabaseTable::StructureElements);

        return $this->db->table($users)
            ->join($elements, $elements . '.id', '=', $users . '.id')
            ->where($elements . '.structureType', '=', StructureType::User->value);
    }
}
