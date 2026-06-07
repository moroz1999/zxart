<?php

declare(strict_types=1);

namespace ZxArt\Groups\Rest;

readonly class GroupLocationRestDto
{
    public function __construct(
        public ?GroupLocationItemRestDto $city,
        public ?GroupLocationItemRestDto $country,
    ) {
    }
}
