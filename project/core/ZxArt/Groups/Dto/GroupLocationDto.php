<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Groups\Rest\GroupLocationRestDto;

#[Map(target: GroupLocationRestDto::class)]
readonly class GroupLocationDto
{
    public function __construct(
        public ?GroupLocationItemDto $city,
        public ?GroupLocationItemDto $country,
    ) {
    }
}
