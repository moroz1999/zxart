<?php

declare(strict_types=1);

namespace ZxArt\Users\Dbal;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\Dbal\AbstractDbal;
use ZxArt\Shared\DatabaseTable;

/**
 * User groups and who belongs to them: the `userRelation` links from a group
 * to its users.
 *
 * @psalm-type UserGroupRow = array{id: int, groupName: string, description: string}
 */
final readonly class UserGroupsDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return list<UserGroupRow> by name
     */
    public function getGroups(): array
    {
        /** @var list<array{id: int|string, groupName: string, description: string|null}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::UserGroup))
            ->orderBy('groupName')
            ->get(['id', 'groupName', 'description']);

        return array_map(static fn(array $row): array => [
            'id' => (int)$row['id'],
            'groupName' => $row['groupName'],
            'description' => (string)$row['description'],
        ], $rows);
    }

    /**
     * @return array<int, int> groupId => members
     */
    public function getMembersCounts(): array
    {
        /** @var list<array{parentStructureId: int|string, amount: int|string}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->select('parentStructureId')
            ->selectRaw('COUNT(*) AS amount')
            ->where('type', '=', LinkTypes::USER_RELATION->value)
            ->groupBy('parentStructureId')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int)$row['parentStructureId']] = (int)$row['amount'];
        }

        return $counts;
    }

    /**
     * @param list<int> $userIds
     * @return array<int, list<int>> userId => its group ids, sorted
     */
    public function getGroupIdsOfUsers(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }
        /** @var list<array{childStructureId: int|string, parentStructureId: int|string}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('type', '=', LinkTypes::USER_RELATION->value)
            ->whereIn('childStructureId', $userIds)
            ->orderBy('parentStructureId')
            ->get(['childStructureId', 'parentStructureId']);

        $groupIds = [];
        foreach ($rows as $row) {
            $groupIds[(int)$row['childStructureId']][] = (int)$row['parentStructureId'];
        }

        return $groupIds;
    }

    /**
     * @param list<int> $groupIds
     * @return list<int> those of the ids that are user groups
     */
    public function findExistingGroupIds(array $groupIds): array
    {
        if ($groupIds === []) {
            return [];
        }
        /** @var list<int|string> $ids */
        $ids = $this->db->table($this->tableName(DatabaseTable::UserGroup))
            ->whereIn('id', $groupIds)
            ->pluck('id');

        return array_map('intval', $ids);
    }

    /**
     * Makes the user a member of exactly the given groups.
     *
     * @param list<int> $groupIds
     */
    public function replaceGroupsOfUser(int $userId, array $groupIds): void
    {
        $links = $this->tableName(DatabaseTable::StructureLinks);
        $this->db->table($links)
            ->where('childStructureId', '=', $userId)
            ->where('type', '=', LinkTypes::USER_RELATION->value)
            ->delete();
        foreach ($groupIds as $groupId) {
            $this->db->table($links)->insert([
                'parentStructureId' => $groupId,
                'childStructureId' => $userId,
                'type' => LinkTypes::USER_RELATION->value,
            ]);
        }
    }
}
