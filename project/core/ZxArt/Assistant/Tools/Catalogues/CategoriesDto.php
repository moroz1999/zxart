<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Catalogues;

/**
 * @psalm-api
 */
final readonly class CategoriesDto
{
    /**
     * @param list<CategoryDto> $categories depth first, each after its parent
     */
    public function __construct(
        public array $categories,
    ) {
    }
}
