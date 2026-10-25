<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Fixtures;

final readonly class UntypedRequestDto
{
    public function __construct(
        public mixed $anything,
    ) {
    }
}
