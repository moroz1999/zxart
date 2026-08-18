<?php

declare(strict_types=1);

namespace ZxArt\ZxProdCategories;

use structureManager;
use zxProdCategoryElement;

/**
 * Resolves a category to every category id beneath it, itself included.
 *
 * Filtering by a section always means the whole section: "Demoscene" covers
 * megademos, intros and trackmos, "Games" covers every genre, "Press" covers
 * every magazine. Anything that narrows works by category must go through this
 * service, so no caller ends up matching only the section's own link and
 * silently losing most of what belongs to it.
 */
readonly class ProdCategoryTreeService
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @return int[] the category and its whole subtree; just the category
     *               itself when it has none or cannot be resolved
     */
    public function getTreeIds(int $categoryId): array
    {
        $element = $this->structureManager->getElementById($categoryId);
        if (!$element instanceof zxProdCategoryElement) {
            return [$categoryId];
        }

        /** @var int[] $ids */
        $ids = [];
        $element->getSubCategoriesTreeIds($ids);

        return $ids !== [] ? $ids : [$categoryId];
    }

    /**
     * @param int[] $categoryIds
     * @return int[] the union of every subtree, without duplicates
     */
    public function expandAll(array $categoryIds): array
    {
        $expanded = [];
        foreach ($categoryIds as $categoryId) {
            $expanded = array_merge($expanded, $this->getTreeIds($categoryId));
        }

        return array_values(array_unique($expanded));
    }
}
