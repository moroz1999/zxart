<?php

declare(strict_types=1);

namespace ZxArt\Tests\ZxProdCategories;

use LanguagesManager;
use linksManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\ZxProdCategories\CategoryManageService;
use ZxArt\ZxProdCategories\Dto\CategorySaveDto;
use ZxArt\ZxProdCategories\Exception\CategoryException;
use ZxArt\ZxProdCategories\Repositories\CategoryManageRepository;
use zxProdCategoryElement;

/**
 * The tree the management screen is built from, and the guards that keep a
 * branch from being deleted out from under its contents.
 */
#[AllowMockObjectsWithoutExpectations]
class CategoryManageServiceTest extends TestCase
{
    private const int RUSSIAN = 930;
    private const int ENGLISH = 2105;

    private const int TREE_ROOT = 1;
    /** The ids the fixture tree is built from. */
    private const array CATEGORY_IDS = [10 => true, 11 => true, 12 => true, 20 => true];

    private CategoryManageRepository&MockObject $repository;
    private structureManager&MockObject $structureManager;
    private linksManager&MockObject $linksManager;
    private CategoryManageService $service;
    /** @var array<int, zxProdCategoryElement&MockObject> */
    private array $elements = [];

    protected function setUp(): void
    {
        $this->repository = $this->createMock(CategoryManageRepository::class);
        $this->structureManager = $this->createMock(structureManager::class);
        $this->linksManager = $this->createMock(linksManager::class);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getLanguagesIdList')->willReturn([self::RUSSIAN, self::ENGLISH]);
        $languagesManager->method('getCurrentLanguageId')->willReturn(self::ENGLISH);

        $this->service = new CategoryManageService(
            $this->repository,
            $this->structureManager,
            $this->linksManager,
            $languagesManager,
        );

        $this->repository->method('getTitles')->willReturn([
            10 => [self::RUSSIAN => 'Игры', self::ENGLISH => 'Games'],
            11 => [self::RUSSIAN => 'Аркады', self::ENGLISH => 'Arcade'],
            12 => [self::RUSSIAN => 'Платформеры', self::ENGLISH => 'Platformers'],
            20 => [self::RUSSIAN => 'Демосцена', self::ENGLISH => 'Demoscene'],
        ]);
        // 1 is the tree root and carries no title of its own
        $this->repository->method('getTreeLinks')->willReturn([
            ['parentId' => 1, 'categoryId' => 10],
            ['parentId' => 1, 'categoryId' => 20],
            ['parentId' => 10, 'categoryId' => 11],
            ['parentId' => 11, 'categoryId' => 12],
        ]);
        $this->repository->method('getProdCounts')->willReturn([10 => 5, 20 => 2]);

        $treeRoot = $this->createMock(structureElement::class);
        $treeRoot->method('getId')->willReturn(self::TREE_ROOT);
        $this->structureManager->method('getElementByMarker')->willReturn($treeRoot);
        // no catalogue is projected in the unit context, so no softCatalogue link
        $this->structureManager->method('getElementsByType')->willReturn([]);
        // one element per id, so a parent lookup answers with the parent
        $this->structureManager->method('getElementById')->willReturnCallback(
            fn(int $id): ?zxProdCategoryElement => $this->categoryElement($id),
        );
    }

    public function testTreeIsDepthFirstWithLevelsAndParents(): void
    {
        $nodes = $this->service->getTree();

        $this->assertSame(
            [[10, 0, null], [11, 1, 10], [12, 2, 11], [20, 0, null]],
            array_map(static fn($node): array => [$node->id, $node->level, $node->parentId], $nodes),
        );
    }

    public function testNodeCarriesEveryLanguageAndTheCurrentOneAsItsTitle(): void
    {
        $games = $this->service->getTree()[0];

        $this->assertSame('Games', $games->title);
        $this->assertSame([self::RUSSIAN => 'Игры', self::ENGLISH => 'Games'], $games->titles);
    }

    public function testNodeCountsItsOwnProductionsAndDirectChildren(): void
    {
        [$games, $arcade, $platformers, $demoscene] = $this->service->getTree();

        $this->assertSame([5, 1], [$games->prods, $games->subCategories]);
        $this->assertSame([0, 1], [$arcade->prods, $arcade->subCategories]);
        $this->assertSame([0, 0], [$platformers->prods, $platformers->subCategories]);
        $this->assertSame([2, 0], [$demoscene->prods, $demoscene->subCategories]);
    }

    public function testNodeAlsoCountsTheWholeBranchBeneathIt(): void
    {
        [$games, $arcade, $platformers, $demoscene] = $this->service->getTree();

        $this->assertSame(5, $games->prodsTotal);
        $this->assertSame(0, $arcade->prodsTotal);
        $this->assertSame(0, $platformers->prodsTotal);
        $this->assertSame(2, $demoscene->prodsTotal);
    }

    public function testABranchTotalAddsUpThroughEveryLevel(): void
    {
        $repository = $this->createMock(CategoryManageRepository::class);
        $repository->method('getTitles')->willReturn([
            10 => [self::ENGLISH => 'Games'],
            11 => [self::ENGLISH => 'Arcade'],
            12 => [self::ENGLISH => 'Platformers'],
        ]);
        $repository->method('getTreeLinks')->willReturn([
            ['parentId' => 1, 'categoryId' => 10],
            ['parentId' => 10, 'categoryId' => 11],
            ['parentId' => 11, 'categoryId' => 12],
        ]);
        $repository->method('getProdCounts')->willReturn([10 => 1, 11 => 20, 12 => 300]);

        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getCurrentLanguageId')->willReturn(self::ENGLISH);
        $service = new CategoryManageService(
            $repository,
            $this->structureManager,
            $this->linksManager,
            $languagesManager,
        );

        $this->assertSame(
            [[1, 321], [20, 320], [300, 300]],
            array_map(static fn($node): array => [$node->prods, $node->prodsTotal], $service->getTree()),
        );
    }

    public function testCategoryHoldingProductionsIsNotDeleted(): void
    {

        $this->expectException(CategoryException::class);
        $this->expectExceptionMessage('still holds 5 production(s)');

        $this->service->delete(10);
    }

    public function testCategoryWithSubcategoriesIsNotDeleted(): void
    {

        $this->expectException(CategoryException::class);
        $this->expectExceptionMessage('still has subcategories');

        // 11 holds no productions of its own, but 12 hangs under it
        $this->service->delete(11);
    }

    public function testAnEmptyLeafIsDeleted(): void
    {
        $this->categoryElement(12)?->expects($this->once())->method('deleteElementData');

        $this->service->delete(12);
    }

    public function testUnknownCategoryIsReportedAsNotFound(): void
    {

        $this->expectException(CategoryException::class);
        $this->expectExceptionMessage('Category not found');

        $this->service->delete(999);
    }

    public function testUpdateRefusesATitleMissingInOneLanguage(): void
    {
        $this->expectException(CategoryException::class);
        $this->expectExceptionMessage('Category title is required for language ' . self::ENGLISH);

        $this->service->update(new CategorySaveDto(titles: [self::RUSSIAN => 'Игры'], id: 10));
    }

    public function testUpdateRefusesABlankTitle(): void
    {
        $this->expectException(CategoryException::class);

        $this->service->update(
            new CategorySaveDto(titles: [self::RUSSIAN => 'Игры', self::ENGLISH => '   '], id: 10),
        );
    }

    public function testUpdateWithoutAnIdIsABadRequest(): void
    {
        $this->expectException(CategoryException::class);
        $this->expectExceptionMessage('Missing required field: id');

        $this->service->update(new CategorySaveDto(titles: [self::RUSSIAN => 'Игры', self::ENGLISH => 'Games']));
    }

    public function testMovingACategoryIntoItsOwnSubtreeIsRefused(): void
    {

        $this->expectException(CategoryException::class);
        $this->expectExceptionMessage('cannot be moved into its own subtree');

        // 12 sits two levels under 10
        $this->service->update($this->save(10, 12));
    }

    public function testMovingToTopLevelRelinksUnderTheTreeContainer(): void
    {

        $this->linksManager->expects($this->once())
            ->method('unLinkElements')
            ->with(10, 11, 'structure');
        $this->linksManager->expects($this->once())
            ->method('linkElements')
            ->with(self::TREE_ROOT, 11, 'structure');

        $this->service->update($this->save(11, null));
    }

    public function testMovingUnderAnotherCategoryRelinksThere(): void
    {

        $this->linksManager->expects($this->once())
            ->method('unLinkElements')
            ->with(self::TREE_ROOT, 20, 'structure');
        $this->linksManager->expects($this->once())
            ->method('linkElements')
            ->with(10, 20, 'structure');

        $this->service->update($this->save(20, 10));
    }

    public function testRenamingInPlaceTouchesNoLinks(): void
    {

        $this->linksManager->expects($this->never())->method('unLinkElements');
        $this->linksManager->expects($this->never())->method('linkElements');

        $this->service->update($this->save(11, 10));
    }

    public function testATopLevelCategoryStaysPutWhenItsParentIsStillNull(): void
    {

        $this->linksManager->expects($this->never())->method('unLinkElements');
        $this->linksManager->expects($this->never())->method('linkElements');

        $this->service->update($this->save(10, null));
    }

    /**
     * The element behind an id of the fixture; an unknown id has none, exactly
     * as the structure manager answers for one.
     */
    private function categoryElement(int $id): ?zxProdCategoryElement
    {
        if (!isset(self::CATEGORY_IDS[$id])) {
            return null;
        }
        if (!isset($this->elements[$id])) {
            $element = $this->createMock(zxProdCategoryElement::class);
            $element->method('getId')->willReturn($id);
            $element->method('importExternalData')->willReturn(true);
            $this->elements[$id] = $element;
        }

        return $this->elements[$id];
    }

    /**
     * @param array<int, string>|null $titles
     */
    private function save(int $id, ?int $parentId, ?array $titles = null): CategorySaveDto
    {
        return new CategorySaveDto(
            titles: $titles ?? [self::RUSSIAN => 'Игры', self::ENGLISH => 'Games'],
            id: $id,
            parentId: $parentId,
        );
    }
}
