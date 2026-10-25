<?php

declare(strict_types=1);

namespace ZxArt\Prods\Repositories;

use structureManager;
use ZxArt\Prods\Exception\ProdDataException;
use zxProdElement;

/**
 * Supplies productions to the use cases and stores them: every write of a
 * production's own data, from the editors and the imports alike, goes through
 * `save()`.
 *
 * @psalm-api
 */
final readonly class ProdRepository
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws ProdDataException 400 without an id, 404 for no production of it
     */
    public function get(int $prodId): zxProdElement
    {
        if ($prodId <= 0) {
            throw new ProdDataException('Missing required production id', 400);
        }
        $prod = $this->structureManager->getElementById($prodId);
        if (!$prod instanceof zxProdElement) {
            throw new ProdDataException('Production not found', 404);
        }

        return $prod;
    }

    /**
     * A new production under the category, its structure record not yet stored. A
     * production's categories are its structural parents; the editors link it
     * the way the request sets, the imports as `zxProdCategory`.
     *
     * @throws ProdDataException 500 when the element cannot be made
     */
    public function create(int $categoryId, ?string $linkType = null): zxProdElement
    {
        $prod = $this->structureManager->createElement('zxProd', 'show', $categoryId, false, $linkType);
        if (!$prod instanceof zxProdElement) {
            throw new ProdDataException('The production could not be created', 500);
        }

        return $prod;
    }

    public function save(zxProdElement $prod): void
    {
        $prod->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element:
     * authorship, files.
     */
    public function touch(zxProdElement $prod): void
    {
        $prod->persistModificationTime();
    }

    public function delete(zxProdElement $prod): void
    {
        $prod->deleteElementData();
    }
}
