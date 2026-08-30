<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Authors\Rest\ClaimApprovalRestDto;

/**
 * The claim a moderator is asked to approve: who claims which author, whether
 * the visitor holding the link may approve it, and whether it already holds.
 */
#[Map(target: ClaimApprovalRestDto::class)]
final readonly class ClaimApprovalDto
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
