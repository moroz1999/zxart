<?php

declare(strict_types=1);

namespace ZxArt\Parties\Repositories;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;

readonly final class PartyAuthorsRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    public function countByPartyId(int $partyId): int
    {
        $workIdsByLinkType = $this->getPartyWorkIdsByLinkType($partyId);

        $authorIds = [
            ...$this->getLinkedAuthorIds(
                $workIdsByLinkType[LinkTypes::PARTY_PICTURE->value],
                LinkTypes::AUTHOR_PICTURE,
            ),
            ...$this->getLinkedAuthorIds(
                $workIdsByLinkType[LinkTypes::PARTY_MUSIC->value],
                LinkTypes::AUTHOR_MUSIC,
            ),
            ...$this->getSoftwareAuthorIds($workIdsByLinkType[LinkTypes::PARTY_PROD->value]),
        ];

        return count($this->normalizeAuthorIds($authorIds));
    }

    /**
     * @return array{partyPicture: int[], partyMusic: int[], partyProd: int[]}
     */
    private function getPartyWorkIdsByLinkType(int $partyId): array
    {
        $workIdsByLinkType = [
            LinkTypes::PARTY_PICTURE->value => [],
            LinkTypes::PARTY_MUSIC->value => [],
            LinkTypes::PARTY_PROD->value => [],
        ];

        /** @var list<array{childStructureId: int, type: string}> $workLinks */
        $workLinks = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('parentStructureId', '=', $partyId)
            ->whereIn('type', array_keys($workIdsByLinkType))
            ->distinct()
            ->get(['childStructureId', 'type']);

        foreach ($workLinks as $workLink) {
            switch ($workLink['type']) {
                case LinkTypes::PARTY_PICTURE->value:
                    $workIdsByLinkType[LinkTypes::PARTY_PICTURE->value][] = $workLink['childStructureId'];
                    break;
                case LinkTypes::PARTY_MUSIC->value:
                    $workIdsByLinkType[LinkTypes::PARTY_MUSIC->value][] = $workLink['childStructureId'];
                    break;
                case LinkTypes::PARTY_PROD->value:
                    $workIdsByLinkType[LinkTypes::PARTY_PROD->value][] = $workLink['childStructureId'];
                    break;
            }
        }

        return $workIdsByLinkType;
    }

    /**
     * @param int[] $workIds
     * @return int[]
     */
    private function getLinkedAuthorIds(array $workIds, LinkTypes $linkType): array
    {
        if ($workIds === []) {
            return [];
        }

        /** @var int[] $authorIds */
        $authorIds = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->whereIn('childStructureId', $workIds)
            ->where('type', '=', $linkType->value)
            ->distinct()
            ->pluck('parentStructureId');

        return $authorIds;
    }

    /**
     * @param int[] $workIds
     * @return int[]
     */
    private function getSoftwareAuthorIds(array $workIds): array
    {
        if ($workIds === []) {
            return [];
        }

        /** @var int[] $authorIds */
        $authorIds = $this->db->table($this->tableName(DatabaseTable::Authorship))
            ->whereIn('elementId', $workIds)
            ->whereIn('type', [EntityType::Prod->value, EntityType::Release->value])
            ->distinct()
            ->pluck('authorId');

        return $authorIds;
    }

    /**
     * @param int[] $authorIds
     * @return int[]
     */
    private function normalizeAuthorIds(array $authorIds): array
    {
        if ($authorIds === []) {
            return [];
        }

        /** @var list<array{id: int, authorId: int}> $aliases */
        $aliases = $this->db->table($this->tableName(DatabaseTable::AuthorAlias))
            ->whereIn('id', $authorIds)
            ->get(['id', 'authorId']);

        $mainAuthorIdsByAliasId = [];
        foreach ($aliases as $alias) {
            $mainAuthorIdsByAliasId[$alias['id']] = $alias['authorId'];
        }

        $normalizedIds = [];
        foreach ($authorIds as $authorId) {
            $normalizedId = $mainAuthorIdsByAliasId[$authorId] ?? $authorId;
            $normalizedIds[$normalizedId] = $normalizedId;
        }

        return array_values($normalizedIds);
    }
}
