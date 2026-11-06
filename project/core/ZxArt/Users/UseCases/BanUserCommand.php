<?php

declare(strict_types=1);

namespace ZxArt\Users\UseCases;

final readonly class BanUserCommand
{
    public function __construct(
        public int $userId,
    ) {
    }
}
