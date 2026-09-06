<?php

declare(strict_types=1);

namespace ZxArt\ZxProdCategories\Dto;

/**
 * One category of the management tree.
 *
 * The tree is served as a flat, depth-first list carrying `parentId` and
 * `level` — the shape `/formdata/` already hands the category picker — so the
 * payload stays a plain list and the client owns the nesting.
 *
 * `titles` carries every interface language at once: the tree and the edit form
 * are answered by the same request, exactly as the hardware catalog is.
 */
readonly class CategoryNodeDto
{
    /**
     * @param array<int, string> $titles keyed by language id
     * @param int $prods productions filed under this category itself
     * @param int $prodsTotal the same, plus everything in its subcategories
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
