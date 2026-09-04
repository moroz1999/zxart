<?php

declare(strict_types=1);

namespace ZxArt\Import;

use ZxArt\Import\Repositories\ImportOriginsRepository;

/**
 * Reading and saving of the portal ids of one element. The form submits the
 * whole list under `importOrigins`, so ids it no longer carries are dropped.
 *
 * @see ImportOriginsHolder
 */
trait ImportOriginsHolderTrait
{
    /**
     * A form draft carries none: it is not persisted yet, and its transient
     * identifier is a structure path that casts to 0 - the id of no element.
     *
     * @return list<array{origin: string, importId: string}>
     */
    public function getImportOrigins(): array
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
        return $this->getService(ImportOriginsRepository::class)->getElementOrigins($this->getId());
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function getImportOriginOptions(): array
    {
        $options = [];
        foreach (ImportOrigin::cases() as $origin) {
            $options[] = ['value' => $origin->value, 'label' => $origin->title()];
        }
        return $options;
    }

    public function persistImportOrigins(): void
    {
        $repository = $this->getService(ImportOriginsRepository::class);
        $elementId = $this->getPersistedId();
        $submitted = $this->getSubmittedImportOrigins();

        $existing = [];
        foreach ($repository->getElementOrigins($elementId) as $row) {
            $existing[$row['origin'] . '|' . $row['importId']] = $row;
        }

        foreach ($submitted as $key => $row) {
            if (isset($existing[$key])) {
                continue;
            }
            $repository->saveOrigin($elementId, $row['origin'], $row['importId'], $this->getImportEntityType());
        }
        foreach ($existing as $key => $row) {
            if (!isset($submitted[$key])) {
                $repository->deleteOrigin($elementId, $row['origin'], $row['importId']);
            }
        }
    }

    /**
     * Submitted rows, indexed by portal and id so the same pair sent twice
     * counts once.
     *
     * @return array<string, array{origin: ImportOrigin, importId: string}>
     */
    private function getSubmittedImportOrigins(): array
    {
        $submitted = [];
        /** @var array<array-key, array<string, mixed>> $rows the chunk holds the rows exactly as they were posted */
        $rows = (array)$this->getValue('importOrigins');
        foreach ($rows as $row) {
            $origin = ImportOrigin::tryFrom((string)($row['origin'] ?? ''));
            $importId = trim((string)($row['importId'] ?? ''));
            if ($origin === null || $importId === '') {
                continue;
            }
            $submitted[$origin->value . '|' . $importId] = ['origin' => $origin, 'importId' => $importId];
        }
        return $submitted;
    }
}
