<?php

declare(strict_types=1);

namespace ZxArt\Import\Repositories;

use Illuminate\Database\Connection;
use ZxArt\Import\ImportOrigin;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Repositories\AbstractRepository;

/**
 * Portal ids an element carries. One element may hold several ids of the same
 * portal, so a record is identified by the portal and the id together.
 */
final readonly class ImportOriginsRepository extends AbstractRepository
{
    public function __construct(private Connection $db)
    {
    }

    /**
     * @return list<array{origin: string, importId: string}>
     */
    public function getElementOrigins(int $elementId): array
    {
        /** @var list<array{importOrigin: string, importId: string}> $rows */
        $rows = $this->db->table($this->tableName(DatabaseTable::ImportOrigin))
            ->select(['importOrigin', 'importId'])
            ->where('elementId', '=', $elementId)
            ->orderBy('importOrigin')
            ->orderBy('importId')
            ->get();

        $origins = [];
        foreach ($rows as $row) {
            $origins[] = [
                'origin' => $row['importOrigin'],
                'importId' => $row['importId'],
            ];
        }
        return $origins;
    }

    /**
     * Points the portal id at this element. The same id of the same portal can
     * only describe one element of a type, so an existing record is moved
     * instead of duplicated.
     */
    public function saveOrigin(int $elementId, ImportOrigin $origin, string $importId, EntityType $type): void
    {
        $this->db->table($this->tableName(DatabaseTable::ImportOrigin))
            ->updateOrInsert(
                [
                    'importOrigin' => $origin->value,
                    'importId' => $importId,
                    'type' => $type->value,
                ],
                ['elementId' => $elementId],
            );
    }

    public function deleteOrigin(int $elementId, string $origin, string $importId): void
    {
        $this->db->table($this->tableName(DatabaseTable::ImportOrigin))
            ->where('elementId', '=', $elementId)
            ->where('importOrigin', '=', $origin)
            ->where('importId', '=', $importId)
            ->delete();
    }
}
