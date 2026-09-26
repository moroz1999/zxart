<?php

declare(strict_types=1);

namespace ZxArt\EntityConversion\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\EntityConversion\Rest\ConvertedEntityRestDto;

#[Map(target: ConvertedEntityRestDto::class)]
final readonly class ConvertedEntityDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
