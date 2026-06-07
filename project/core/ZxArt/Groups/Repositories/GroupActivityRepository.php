<?php

declare(strict_types=1);

namespace ZxArt\Groups\Repositories;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class GroupActivityRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    /**
     * @return int[]
     */
    public function getGroupWorkIds(int $groupId): array
    {
        $groupIds = $this->getGroupAndAliasIds($groupId);

        /** @var int[] $childIds */
        $childIds = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->whereIn('parentStructureId', $groupIds)
            ->whereIn('type', [
                LinkTypes::ZX_PROD_GROUPS->value,
                LinkTypes::ZX_PROD_PUBLISHERS->value,
                LinkTypes::ZX_RELEASE_PUBLISHERS->value,
            ])
            ->distinct()
            ->pluck('childStructureId');

        return array_values(array_unique($childIds));
    }

    /**
     * @param int[] $workIds
     */
    public function countVotes(array $workIds): int
    {
        if ($workIds === []) {
            return 0;
        }

        return $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->whereIn('elementId', $workIds)
            ->where('value', '!=', 0)
            ->count();
    }

    /**
     * @param int[] $workIds
     * @return list<array{elementId: int, userId: int, value: int, date: int}>
     */
    public function findVotesPaged(array $workIds, int $offset, int $limit): array
    {
        if ($workIds === []) {
            return [];
        }

        /** @var list<array{elementId: int, userId: int, value: int, date: int}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::VotesHistory))
            ->whereIn('elementId', $workIds)
            ->where('value', '!=', 0)
            ->orderBy('date', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get(['elementId', 'userId', 'value', 'date']);

        return $rows;
    }

    /**
     * @param int[] $workIds
     */
    public function countComments(array $workIds): int
    {
        if ($workIds === []) {
            return 0;
        }

        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $commentTable = $this->tableName(DatabaseTable::Comment);

        return $this->db->table($linksTable)
            ->join($commentTable, "$commentTable.id", '=', "$linksTable.childStructureId")
            ->whereIn("$linksTable.parentStructureId", $workIds)
            ->where("$linksTable.type", '=', LinkTypes::COMMENT_TARGET->value)
            ->where("$commentTable.approved", '=', 1)
            ->distinct()
            ->count("$commentTable.id");
    }

    /**
     * @param int[] $workIds
     * @return int[]
     */
    public function findCommentIdsPaged(array $workIds, int $offset, int $limit): array
    {
        if ($workIds === []) {
            return [];
        }

        $linksTable = $this->tableName(DatabaseTable::StructureLinks);
        $commentTable = $this->tableName(DatabaseTable::Comment);
        $structureElementsTable = $this->tableName(DatabaseTable::StructureElements);

        /** @var list<array{commentId: int}> $rows */
        $rows = $this->db->table($linksTable)
            ->join($commentTable, "$commentTable.id", '=', "$linksTable.childStructureId")
            ->join($structureElementsTable, "$structureElementsTable.id", '=', "$commentTable.id")
            ->whereIn("$linksTable.parentStructureId", $workIds)
            ->where("$linksTable.type", '=', LinkTypes::COMMENT_TARGET->value)
            ->where("$commentTable.approved", '=', 1)
            ->orderBy("$structureElementsTable.dateCreated", 'desc')
            ->offset($offset)
            ->limit($limit)
            ->distinct()
            ->get(["$commentTable.id as commentId"]);

        return array_map(static fn(array $row): int => (int)$row['commentId'], $rows);
    }

    /**
     * @return int[]
     */
    private function getGroupAndAliasIds(int $groupId): array
    {
        /** @var int[] $aliasIds */
        $aliasIds = $this->db->table($this->tableName(DatabaseTable::GroupAlias))
            ->where('groupId', '=', $groupId)
            ->pluck('id');

        return [$groupId, ...$aliasIds];
    }
}
