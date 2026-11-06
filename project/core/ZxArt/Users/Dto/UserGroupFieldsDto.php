<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class UserGroupFieldsDto
{
    public function __construct(
        public string $groupName,
        public string $description = '',
    ) {
    }
}
