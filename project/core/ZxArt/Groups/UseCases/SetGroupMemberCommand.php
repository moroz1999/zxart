<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

final readonly class SetGroupMemberCommand
{
    /**
     * @param list<string> $roles none stands for an unknown role
     * @param int|null $startDate null keeps the stored date, 0 clears it
     * @param int|null $endDate null keeps the stored date, 0 clears it
     */
    public function __construct(
        public int $groupId,
        public int $authorId,
        public array $roles,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }
}
