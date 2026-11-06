<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

final readonly class UserDetailsDto
{
    /**
     * @param list<int> $groupIds
     */
    public function __construct(
        public int $id,
        public string $userName,
        public string $email,
        public int $dateCreated,
        public bool $verified,
        public bool $banned,
        public bool $commentsAllowed,
        public bool $showEmail,
        public bool $vip,
        public bool $supporter,
        public bool $volunteer,
        public int $authorId,
        public string $authorTitle,
        public array $groupIds,
        public int $commentsCount,
        public int $votesCount,
    ) {
    }
}
