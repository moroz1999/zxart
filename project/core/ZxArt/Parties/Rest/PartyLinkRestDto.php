<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyLinkRestDto
{
    public function __construct(
        public string $url,
        public string $label,
    ) {
    }
}
