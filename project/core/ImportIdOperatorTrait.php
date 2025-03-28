<?php

use Illuminate\Database\Connection;

trait ImportIdOperatorTrait
{
    protected array $cache = [];
    protected array $cacheId = [];

    protected Connection $db;

    public function setDb(Connection $db): void
    {
        $this->db = $db;
    }

    protected structureManager $structureManager;

    public function setStructureManager(structureManager $structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    public function getElementByImportId($importId, $origin, $type): ?structureElement
    {
        if (!isset($this->cache[$origin][$type][$importId])) {
            $this->cache[$origin][$type][$importId] = null;
            if ($id = $this->getElementIdByImportId($importId, $origin, $type)) {
                $this->cache[$origin][$type][$importId] = $this->structureManager->getElementById($id);
            }
        }
        return $this->cache[$origin][$type][$importId];
    }

    public function getElementIdByImportId($importId, $origin, $type = null): ?int
    {
        if (!isset($this->cacheId[$origin][$type][$importId])) {
            $this->cacheId[$origin][$type][$importId] = false;
            $query = $this->db->table('import_origin')
                ->select('elementId')
                ->where('importId', '=', $importId)
                ->where('importOrigin', '=', $origin)
                ->limit(1);
            if ($type !== null) {
                $query->where('type', '=', $type);
            }
            if ($row = $query->first()) {
                $this->cacheId[$origin][$type][$importId] = $row['elementId'];
            }
        }
        return $this->cacheId[$origin][$type][$importId];
    }

    protected function saveImportId($elementId, $importId, $origin, $type): bool
    {
        unset($this->cache[$origin][$type][$importId]);
        $this->cacheId[$origin][$type][$importId] = $elementId;

        $existing = $this->db->table('import_origin')
            ->where('importId', $importId)
            ->where('importOrigin', $origin)
            ->where('type', $type)
            ->first();

        if ($existing) {
            $updated = $this->db->table('import_origin')
                ->where('importId', $importId)
                ->where('importOrigin', $origin)
                ->where('type', $type)
                ->update([
                    'elementId' => $elementId,
                ]);
            return (bool)$updated;
        }

        return $this->db->table('import_origin')->insert([
            'importId' => $importId,
            'elementId' => $elementId,
            'importOrigin' => $origin,
            'type' => $type,
        ]);
    }

    protected function moveImportId($oldElementId, $newElementId, $importId, $origin, $type): int
    {
        unset($this->cache[$origin][$type][$importId]);
        return $this->db
            ->table('import_origin')
            ->where('importId', '=', $importId)
            ->where('elementId', '=', $oldElementId)
            ->where('importOrigin', '=', $origin)
            ->where('type', '=', $type)
            ->update(
                [
                    'elementId' => $newElementId,
                ]
            );
    }
}
