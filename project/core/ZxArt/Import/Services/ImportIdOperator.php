<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

use Illuminate\Database\Connection;
use structureElement;
use structureManager;
use ZxArt\Shared\EntityType;

class ImportIdOperator
{
    private array $cache = [];
    private array $cacheId = [];

    public function __construct(
        protected Connection       $db,
        protected structureManager $structureManager,
    )
    {
    }

    public function getElementByImportId(string $importId, string $origin, ?EntityType $type = null): ?structureElement
    {
        $cacheKey = $origin . '|' . ($type?->value ?? '') . '|' . $importId;

        if (!array_key_exists($cacheKey, $this->cache)) {
            $elementId = $this->getElementIdByImportId($importId, $origin, $type);
            $this->cache[$cacheKey] = null;

            if ($elementId !== null) {
                $this->cache[$cacheKey] = $this->structureManager->getElementById($elementId);
            }
        }

        return $this->cache[$cacheKey];
    }

    public function getElementIdByImportId(
        string      $importId,
        string      $origin,
        ?EntityType $type = null
    ): ?int
    {
        $cacheKey = $origin . '|' . ($type?->value ?? '') . '|' . $importId;

        if (!array_key_exists($cacheKey, $this->cacheId)) {
            $query = $this->db->table('import_origin')
                ->select('elementId')
                ->where('importId', '=', $importId)
                ->where('importOrigin', '=', $origin)
                ->limit(1);

            if ($type !== null) {
                $query->where('type', '=', $type->value);
            }

            $row = $query->first();
            $this->cacheId[$cacheKey] = $row !== null ? (int)$row['elementId'] : null;
        }

        return $this->cacheId[$cacheKey];
    }

    public function saveImportId(int $elementId, string $importId, string $origin, ?EntityType $type = null): bool
    {
        $cacheKey = $origin . '|' . ($type?->value ?? '') . '|' . $importId;

        unset($this->cache[$cacheKey]);
        $this->cacheId[$cacheKey] = $elementId;

        $existing = $this->db->table('import_origin')
            ->where('importId', $importId)
            ->where('importOrigin', $origin)
            ->where('type', $type?->value)
            ->first();

        if ($existing) {
            $updated = $this->db->table('import_origin')
                ->where('importId', $importId)
                ->where('importOrigin', $origin)
                ->where('type', $type?->value)
                ->update([
                    'elementId' => $elementId,
                ]);
            return (bool)$updated;
        }

        return $this->db->table('import_origin')->insert([
            'importId' => $importId,
            'elementId' => $elementId,
            'importOrigin' => $origin,
            'type' => $type?->value,
        ]);
    }

    public function moveImportId(int $oldElementId, int $newElementId, string $importId, string $origin, ?EntityType $type = null): int
    {
        $cacheKey = $origin . '|' . ($type?->value ?? '') . '|' . $importId;

        unset($this->cache[$cacheKey]);
        $this->cacheId[$cacheKey] = $newElementId;

        return $this->db
            ->table('import_origin')
            ->where('importId', '=', $importId)
            ->where('elementId', '=', $oldElementId)
            ->where('importOrigin', '=', $origin)
            ->where('type', '=', $type?->value)
            ->update(
                [
                    'elementId' => $newElementId,
                ]
            );
    }
}