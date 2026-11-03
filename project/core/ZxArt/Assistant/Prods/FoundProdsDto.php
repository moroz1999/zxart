<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Prods;

use ZxArt\Prods\Dto\FoundProdDto;

/**
 * @psalm-api
 */
final readonly class FoundProdsDto
{
    /**
     * @param list<FoundProdDto> $results
     */
    public function __construct(
        public int $total,
        public array $results,
    ) {
    }
}
