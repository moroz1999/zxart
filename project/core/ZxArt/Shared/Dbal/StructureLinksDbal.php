<?php

declare(strict_types=1);

namespace ZxArt\Shared\Dbal;

use Illuminate\Database\Connection;
use ZxArt\LinkTypes;
use ZxArt\Shared\DatabaseTable;

/**
 * Links read as they are stored, not as an element loaded them earlier.
 *
 * @psalm-api
 */
final readonly class StructureLinksDbal extends AbstractDbal
{
    public function __construct(
        private Connection $db,
    ) {
    }

    /**
     * @return int[] in the order they were linked
     */
    public function parentIds(int $childId, LinkTypes $type): array
    {
        /** @var int[] $ids */
        $ids = $this->db->table($this->tableName(DatabaseTable::StructureLinks))
            ->where('childStructureId', '=', $childId)
            ->where('type', '=', $type->value)
            ->orderBy('id')
            ->pluck('parentStructureId');

        return $ids;
    }
}
