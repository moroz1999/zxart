<?php

declare(strict_types=1);

namespace ZxArt\ZxProdCategories\Dto;

/**
 * A create or update request for one category.
 *
 * Denormalized from the request body by `symfony/serializer`, so the controller
 * never handles an untyped array. What the *domain* considers incomplete — a
 * language without a title, a parent that is not a category — is checked by
 * {@see \ZxArt\ZxProdCategories\CategoryManageService}, which can say which
 * field is wrong and why.
 *
 * `parentId` always states where the category belongs — on a creation and on an
 * update alike — and `null` means top-level, so an update that leaves it out
 * moves the category to the top of the tree.
 */
readonly class CategorySaveDto
{
    /**
     * @param array<int, string> $titles keyed by language id
     */
    public function __construct(
        public array $titles = [],
        public ?int $id = null,
        public ?int $parentId = null,
    ) {
    }
}
