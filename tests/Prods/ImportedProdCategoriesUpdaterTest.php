<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use linksManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Services\ImportIdOperator;
use ZxArt\Prods\Services\ImportedProdCategoriesUpdater;

/**
 * An import replaces a production's categories with the ones its source names,
 * but only with categories it can map: a production never loses the category it
 * has to a source category the site does not know.
 */
#[AllowMockObjectsWithoutExpectations]
class ImportedProdCategoriesUpdaterTest extends TestCase
{
    private const int PROD_ID = 500;
    private const int MISC_ID = 92188;
    private const int GAMES_ID = 92170;

    private linksManager&MockObject $linksManager;
    private ImportedProdCategoriesUpdater $updater;

    protected function setUp(): void
    {
        $this->linksManager = $this->createMock(linksManager::class);
        $this->linksManager->method('getConnectedIdList')->willReturn([self::MISC_ID]);
        $importIdOperator = $this->createMock(ImportIdOperator::class);
        $importIdOperator->method('getElementIdByImportId')->willReturnCallback(
            static fn(string $importId): ?int => $importId === 'game' ? self::GAMES_ID : null,
        );
        $this->updater = new ImportedProdCategoriesUpdater($this->linksManager, $importIdOperator);
    }

    public function testUnknownSourceCategoriesKeepTheCurrentCategory(): void
    {
        $this->linksManager->expects($this->never())->method('unLinkElements');
        $this->linksManager->expects($this->never())->method('linkElements');

        $this->updater->update(self::PROD_ID, ['unmapped-genre'], ImportOrigin::Zxdb);
    }

    public function testKnownSourceCategoryReplacesTheCurrentOne(): void
    {
        $this->linksManager->expects($this->once())->method('linkElements')
            ->with(self::GAMES_ID, self::PROD_ID, 'zxProdCategory');
        $this->linksManager->expects($this->once())->method('unLinkElements')
            ->with(self::MISC_ID, self::PROD_ID, 'zxProdCategory');

        $this->updater->update(self::PROD_ID, ['game', 'unmapped-genre'], ImportOrigin::Zxdb);
    }
}
