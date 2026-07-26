<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Users\Rest\UserProfileRestDto;

#[Map(target: UserProfileRestDto::class)]
readonly class UserProfileDto
{
    public function __construct(
        public string $userName,
        public string $email,
    ) {
    }
}
