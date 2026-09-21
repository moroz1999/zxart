<?php

declare(strict_types=1);

namespace ZxArt\Import\Services;

use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Repositories\ImportOriginsRepository;

/**
 * ZxDB entries a production is linked to. ZxDB ids of series and compilations
 * name a group (`tag…`) instead of an entry and are not entries.
 */
final readonly class ZxdbEntryIdsProvider
{
    public function __construct(
        private ImportOriginsRepository $importOriginsRepository,
    ) {
    }

    /**
     * @return list<string>
     */
    public function getEntryIds(int $prodId): array
    {
        $entryIds = [];
        foreach ($this->importOriginsRepository->getElementImportIds($prodId, ImportOrigin::Zxdb) as $importId) {
            if (ctype_digit($importId)) {
                $entryIds[] = $importId;
            }
        }
        return $entryIds;
    }

    public function hasEntry(int $prodId): bool
    {
        return $this->getEntryIds($prodId) !== [];
    }
}
