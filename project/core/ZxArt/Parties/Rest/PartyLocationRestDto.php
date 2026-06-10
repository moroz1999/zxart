<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyLocationRestDto
{
    public function __construct(
        public ?PartyLocationItemRestDto $city,
        public ?PartyLocationItemRestDto $country,
    ) {
    }
}
