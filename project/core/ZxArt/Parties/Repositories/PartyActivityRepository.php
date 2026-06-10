<?php

declare(strict_types=1);

namespace ZxArt\Parties\Repositories;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class PartyActivityRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    /**
     * Link types connecting a party (parent) to its competition works (children).
     *
     * @return string[]
     */
    private function workLinkTypes(): array
    {
        return [
            LinkTypes::PARTY_PICTURE->value,
            LinkTypes::PARTY_MUSIC->value,
            LinkTypes::PARTY_PROD->value,
        ];
    }

    /**
     * @return int[]
     */
    public function getPartyWorkIds(int $partyId): array
    {
        /** @var int[] $childIds */
        $childIds = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('parentStructureId', '=', $partyId)
            ->whereIn('type', $this->workLinkTypes())
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
            ->orderBy("$structureElementsTable.dateCreated", 'desc')
            ->offset($offset)
            ->limit($limit)
            ->distinct()
            ->get(["$commentTable.id as commentId"]);

        return array_map(static fn(array $row): int => (int)$row['commentId'], $rows);
    }
}
