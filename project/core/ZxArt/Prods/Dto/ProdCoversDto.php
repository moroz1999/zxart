<?php

declare(strict_types=1);

namespace ZxArt\Prods\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Prods\Rest\ProdCoversRestDto;

#[Map(target: ProdCoversRestDto::class)]
readonly class ProdCoversDto
{
    /**
     * @param ProdCoverGroupDto[] $groups
     */
    public function __construct(
        public array $groups,
    ) {
    }
}
