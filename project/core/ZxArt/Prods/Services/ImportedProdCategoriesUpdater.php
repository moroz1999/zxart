<?php

declare(strict_types=1);

namespace ZxArt\Prods\Services;

use linksManager;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\LinkTypes;
use ZxArt\Shared\EntityType;

/**
 * Sets a production's categories to the ones an import source names. Source
 * categories the site cannot map are ignored, and when none of them maps the
 * production keeps the categories it has: a production without a category has
 * no page.
 *
 * @psalm-api
 */
final readonly class ImportedProdCategoriesUpdater
{
    public function __construct(
        private linksManager $linksManager,
        private ImportIdOperator $importIdOperator,
    ) {
    }

    /**
     * @param array<int|string> $importCategoryIds
     */
    public function update(int $prodId, array $importCategoryIds, ImportOrigin $origin): void
    {
        $categoryIds = [];
        foreach ($importCategoryIds as $importCategoryId) {
            $categoryId = $this->importIdOperator->getElementIdByImportId((string)$importCategoryId, $origin, EntityType::Category);
            if ($categoryId !== null) {
                $categoryIds[$categoryId] = $categoryId;
            }
        }
        if ($categoryIds === []) {
            return;
        }

        $linkType = LinkTypes::ZX_PROD_CATEGORY->value;
        /** @var array<int|string> $linkedIds */
        $linkedIds = $this->linksManager->getConnectedIdList($prodId, $linkType, 'child');
        $currentIds = array_map('intval', $linkedIds);
        foreach ($categoryIds as $categoryId) {
            if (!in_array($categoryId, $currentIds, true)) {
                $this->linksManager->linkElements($categoryId, $prodId, $linkType);
            }
        }
        foreach ($currentIds as $currentId) {
            if (!isset($categoryIds[$currentId])) {
                $this->linksManager->unLinkElements($currentId, $prodId, $linkType);
            }
        }
    }
}
