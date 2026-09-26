<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdDataResultRestDto;

/**
 * Outcome of a production change: the id of the element the change produced.
 *
 * @psalm-api
 */
#[Map(target: ProdDataResultRestDto::class)]
final readonly class ProdDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
