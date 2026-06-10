<?php

declare(strict_types=1);

namespace ZxArt\Parties\Rest;

readonly class PartyCompoRestDto
{
    public function __construct(
        public string $compoType,
        public string $medium,
        public string $name,
        public int $count,
    ) {
    }
}
