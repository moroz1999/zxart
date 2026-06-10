<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyBreadcrumbRestDto
{
    public function __construct(
        public string $title,
        public string $url,
    ) {
    }
}
