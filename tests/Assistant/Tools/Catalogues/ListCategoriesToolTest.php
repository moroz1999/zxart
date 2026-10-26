<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Catalogues;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Catalogues\CategoriesDto;
use ZxArt\Assistant\Tools\Catalogues\CategoryDto;
use ZxArt\Assistant\Tools\Catalogues\ListCategoriesRequestDto;
use ZxArt\Assistant\Tools\Catalogues\ListCategoriesTool;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\ZxProdCategories\CategoryManageService;
use ZxArt\ZxProdCategories\Dto\CategoryNodeDto;

final class ListCategoriesToolTest extends TestCase
{
    public function testItIsTheReadToolNamedListCategories(): void
    {
        $tool = new ListCategoriesTool($this->createStub(CategoryManageService::class));

        self::assertInstanceOf(ReadTool::class, $tool);
        self::assertSame('list_categories', $tool->name());
        self::assertSame(ListCategoriesRequestDto::class, $tool->requestClass());
    }

    public function testItAnswersTheTreeDepthFirstWithTheProductionsOfEachSubtree(): void
    {
        $service = $this->createStub(CategoryManageService::class);
        $service->method('getTree')->willReturn([
            new CategoryNodeDto(id: 1, parentId: null, level: 0, title: 'Games', titles: [1 => 'Games', 2 => 'Игры'], prods: 3, prodsTotal: 40, subCategories: 1),
            new CategoryNodeDto(id: 2, parentId: 1, level: 1, title: 'Arcade', titles: [1 => 'Arcade'], prods: 37, prodsTotal: 37, subCategories: 0),
        ]);

        self::assertEquals(new CategoriesDto([
            new CategoryDto(id: 1, parentId: null, level: 0, title: 'Games', prods: 40),
            new CategoryDto(id: 2, parentId: 1, level: 1, title: 'Arcade', prods: 37),
        ]), (new ListCategoriesTool($service))->execute(new ListCategoriesRequestDto()));
    }
}
