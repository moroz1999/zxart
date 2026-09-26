<?php

declare(strict_types=1);

namespace ZxArt\Prods\Rest;

/**
 * @psalm-api
 */
final readonly class ProdDataResultRestDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
