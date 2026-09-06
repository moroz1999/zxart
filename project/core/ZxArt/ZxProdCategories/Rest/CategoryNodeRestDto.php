<?php

declare(strict_types=1);

namespace ZxArt\ZxProdCategories\Rest;

readonly class CategoryNodeRestDto
{
    /**
     * @param array<int, string> $titles keyed by language id, so the management
     *        form can address a language directly
     */
    public function __construct(
        public int $id,
        public ?int $parentId,
        public int $level,
        public string $title,
        public array $titles,
        public int $prods,
        public int $prodsTotal,
        public int $subCategories,
    ) {
    }
}
