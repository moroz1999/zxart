<?php

declare(strict_types=1);

namespace ZxArt\Authors\Rest;

final readonly class ClaimApprovalRestDto
{
    public function __construct(
        public int $authorId,
        public string $authorTitle,
        public string $authorUrl,
        public int $userId,
        public string $userName,
        public bool $canApprove,
        public bool $approved,
    ) {
    }
}
