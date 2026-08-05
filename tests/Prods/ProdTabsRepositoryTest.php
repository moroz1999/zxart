<?php

declare(strict_types=1);

namespace ZxArt\Tests\Prods;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Prods\Repositories\ProdTabsRepository;

/**
 * Each flag in ProdTabsDto corresponds to a specific DB query (or pair of queries for inlays/instructions).
 * The exists() calls follow a fixed order determined by buildTabs():
 *  1.  hasReleases      — structure link STRUCTURE
 *  2.  hasScreenshots   — structure link CONNECTED_FILE
 *  3.  hasCovers       — subquery: release ids → inlayFilesSelector/adFilesSelector links
 *  4.  hasMapFiles      — structure link MAP_FILES_SELECTOR
 *  5.  hasSpeccyMapsUrl — import_origin table (only when #4 is false)
 *  6.  hasRzx           — structure link RZX
 *  7.  hasPictures      — join to module_zxpicture via gameLink
 *  8.  hasTunes         — join to module_zxmusic via gameLink
 *  9.  hasArticles      — parent structure link PROD_ARTICLE
 *  10. hasMentions      — parent structure link PRESS_SOFTWARE
 *  11. hasSeriesProds   — parent structure link SERIES
 *  12. isInSeries       — child structure link SERIES
 *  13. hasCompilations  — symmetric structure link COMPILATION
 *  14. hasInstructions  — subquery: release ids → infoFilesSelector links
 *
 * When hasMapFiles (#4) is true, hasSpeccyMapsUrl is short-circuited, reducing total to 13 calls.
 */
#[AllowMockObjectsWithoutExpectations]
class ProdTabsRepositoryTest extends TestCase
{
    private Builder&MockObject $builder;
    private Connection&MockObject $db;
    private ProdTabsRepository $repository;

    protected function setUp(): void
    {
        $this->builder = $this->createMock(Builder::class);
        $this->builder->method('where')->willReturnSelf();
        $this->builder->method('whereIn')->willReturnSelf();
        $this->builder->method('select')->willReturnSelf();
        $this->builder->method('join')->willReturnSelf();
        $this->builder->method('orWhere')->willReturnSelf();

        $this->db = $this->createMock(Connection::class);
        $this->db->method('table')->willReturn($this->builder);

        $this->repository = new ProdTabsRepository($this->db);
    }

    public function testAllFlagsFalseWhenNoLinksExist(): void
    {
        $this->setExistsResults(array_fill(0, 14, false));

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertFalse($dto->hasReleases);
        $this->assertFalse($dto->hasScreenshots);
        $this->assertFalse($dto->hasCovers);
        $this->assertFalse($dto->hasMaps);
        $this->assertFalse($dto->hasRzx);
        $this->assertFalse($dto->hasPictures);
        $this->assertFalse($dto->hasTunes);
        $this->assertFalse($dto->hasArticles);
        $this->assertFalse($dto->hasMentions);
        $this->assertFalse($dto->hasSeriesProds);
        $this->assertFalse($dto->isInSeries);
        $this->assertFalse($dto->hasCompilations);
        $this->assertFalse($dto->hasDescription);
        $this->assertFalse($dto->hasInstructions);
        $this->assertFalse($dto->hasTextInstructions);
    }

    public function testAllFlagsTrueWhenAllLinksExist(): void
    {
        // hasMapFiles (#4) returns true → hasSpeccyMapsUrl is short-circuited → 13 exists() calls
        $this->setExistsResults(array_fill(0, 13, true));

        $dto = $this->repository->buildTabs(1, true, true);

        $this->assertTrue($dto->hasReleases);
        $this->assertTrue($dto->hasScreenshots);
        $this->assertTrue($dto->hasCovers);
        $this->assertTrue($dto->hasMaps);
        $this->assertTrue($dto->hasRzx);
        $this->assertTrue($dto->hasPictures);
        $this->assertTrue($dto->hasTunes);
        $this->assertTrue($dto->hasArticles);
        $this->assertTrue($dto->hasMentions);
        $this->assertTrue($dto->hasSeriesProds);
        $this->assertTrue($dto->isInSeries);
        $this->assertTrue($dto->hasCompilations);
        $this->assertTrue($dto->hasDescription);
        $this->assertTrue($dto->hasInstructions);
        $this->assertTrue($dto->hasTextInstructions);
    }

    public function testHasReleasesDetectedByStructureLink(): void
    {
        // Position 1: hasReleases = true
        $this->setExistsResults([true, ...array_fill(0, 13, false)]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertTrue($dto->hasReleases);
        $this->assertFalse($dto->hasScreenshots);
        $this->assertFalse($dto->hasMaps);
        $this->assertFalse($dto->hasInstructions);
    }

    public function testHasInlaysDetectedViaReleaseSubquery(): void
    {
        // Position 3: hasCovers = true.
        // hasCoverLinks runs two table() calls: first to get release IDs, then to check cover links.
        $this->setExistsResults([false, false, true, ...array_fill(0, 11, false)]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertFalse($dto->hasReleases);
        $this->assertTrue($dto->hasCovers);
        $this->assertFalse($dto->hasMaps);
        $this->assertFalse($dto->hasInstructions);
    }

    public function testHasMapsDetectedViaImportOriginWhenNoMapFiles(): void
    {
        // Position 4 (hasMapFiles) = false, position 5 (hasSpeccyMapsUrl) = true.
        $this->setExistsResults([false, false, false, false, true, ...array_fill(0, 9, false)]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertFalse($dto->hasReleases);
        $this->assertTrue($dto->hasMaps);
        $this->assertFalse($dto->hasRzx);
    }

    public function testHasMapsShortCircuitsWhenMapFilesLinkExists(): void
    {
        // Position 4 (hasMapFiles) = true → hasSpeccyMapsUrl is NOT queried.
        // Total: 13 exists() calls (no position 5).
        $this->setExistsResults([false, false, false, true, ...array_fill(0, 9, false)]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertTrue($dto->hasMaps);
        $this->assertFalse($dto->hasRzx);
        $this->assertFalse($dto->hasInstructions);
    }

    public function testHasInstructionsDetectedViaReleaseSubquery(): void
    {
        // Position 14: hasInstructions = true (all others false, with hasMapFiles=false so 14 calls).
        // hasInstructionLinks runs two table() calls: release IDs subquery, then infoFilesSelector check.
        $this->setExistsResults([...array_fill(0, 13, false), true]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertFalse($dto->hasReleases);
        $this->assertFalse($dto->hasMaps);
        $this->assertFalse($dto->hasCompilations);
        $this->assertTrue($dto->hasInstructions);
    }

    public function testHasSeriesProdsAndIsInSeriesAreIndependent(): void
    {
        // Position 11 (hasSeriesProds) = true, position 12 (isInSeries) = false
        $this->setExistsResults([false, false, false, false, false, false, false, false, false, false, true, false, false, false]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertTrue($dto->hasSeriesProds);
        $this->assertFalse($dto->isInSeries);
    }

    public function testHasCompilationsDetectedSymmetrically(): void
    {
        // Position 13 (hasCompilations) = true. The query checks both parentStructureId and childStructureId.
        $this->setExistsResults([false, false, false, false, false, false, false, false, false, false, false, false, true, false]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertFalse($dto->hasSeriesProds);
        $this->assertFalse($dto->isInSeries);
        $this->assertTrue($dto->hasCompilations);
        $this->assertFalse($dto->hasInstructions);
    }

    public function testArticlesAndMentionsAreIndependentTabs(): void
    {
        // Position 9 (hasArticles) = false, position 10 (hasMentions) = true: a prod
        // only mentioned by the press gets no articles tab.
        $this->setExistsResults([false, false, false, false, false, false, false, false, false, true, false, false, false, false]);

        $dto = $this->repository->buildTabs(1, false, false);

        $this->assertFalse($dto->hasArticles);
        $this->assertTrue($dto->hasMentions);
    }

    /**
     * @param bool[] $results
     */
    private function setExistsResults(array $results): void
    {
        $index = 0;
        $this->builder->method('exists')->willReturnCallback(
            static function () use (&$index, $results): bool {
                return (bool) ($results[$index++] ?? false);
            }
        );
    }
}
