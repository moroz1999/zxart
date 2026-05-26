<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\AuthorLocationRestDto;

#[Map(target: AuthorLocationRestDto::class)]
readonly class AuthorLocationDto
{
    public function __construct(
        public ?AuthorLocationItemDto $city,
        public ?AuthorLocationItemDto $country,
    ) {
    }
}
