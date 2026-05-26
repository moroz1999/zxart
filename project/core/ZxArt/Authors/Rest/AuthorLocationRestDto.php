<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

readonly class AuthorLocationRestDto
{
    public function __construct(
        public ?AuthorLocationItemRestDto $city,
        public ?AuthorLocationItemRestDto $country,
    ) {
    }
}
