<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

/**
 * @psalm-api
 */
final readonly class SetGroupAliasMemberRequestDto
{
    /**
     * @param int $aliasId the group alias
     * @param int $authorId an author or an author alias
     * @param list<string> $roles member roles from list_options; they replace the member's roles, none stands for unknown
     * @param ?int $startDate when the membership started, a timestamp; null keeps it, 0 clears it
     * @param ?int $endDate when it ended, a timestamp; null keeps it, 0 clears it
     */
    public function __construct(
        public int $aliasId,
        public int $authorId,
        public array $roles,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }
}
