<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

/** The id of the user or user group a write changed. */
final readonly class UserDataResultDto
{
    public function __construct(
        public int $id,
    ) {
    }
}
