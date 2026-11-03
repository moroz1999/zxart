<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Catalogues;

/**
 * @psalm-api
 */
final readonly class CategoryDto
{
    public function __construct(
        public int $id,
        public ?int $parentId,
        /** 0 for a top category */
        public int $level,
        public string $title,
        /** productions in the category and its subcategories */
        public int $prods,
    ) {
    }
}
