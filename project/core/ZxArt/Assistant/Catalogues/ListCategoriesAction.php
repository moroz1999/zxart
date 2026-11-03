<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\NoArgumentsDto;
use ZxArt\ZxProdCategories\CategoryManageService;
use ZxArt\ZxProdCategories\Dto\CategoryNodeDto;

/**
 * @psalm-api
 */
final readonly class ListCategoriesAction implements ReadAction
{
    public function __construct(
        private CategoryManageService $categoryManageService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'list_categories';
    }

    #[Override]
    public function description(): string
    {
        return 'Lists the production categories as a tree, depth first: each with its id, parent, level, title and '
            . 'how many productions it holds with its subcategories. A category filter takes in the subcategories.';
    }

    #[Override]
    public function schema(): string
    {
        return NoArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): CategoriesDto
    {
        return new CategoriesDto(array_map(
            static fn(CategoryNodeDto $node): CategoryDto => new CategoryDto($node->id, $node->parentId, $node->level, $node->title, $node->prodsTotal),
            $this->categoryManageService->getTree(),
        ));
    }
}
