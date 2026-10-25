<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\References\EntityRefDto;

/**
 * A production a typed search found, with what tells it apart.
 *
 * @psalm-api
 */
final readonly class FoundProdDto
{
    /**
     * @param list<CreditDto> $authors
     * @param list<EntityRefDto> $groups the groups that made it
     * @param list<EntityRefDto> $publishers authors or groups
     * @param list<EntityRefDto> $categories
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?int $year,
        public array $authors,
        public array $groups,
        public array $publishers,
        public array $categories,
    ) {
    }
}
