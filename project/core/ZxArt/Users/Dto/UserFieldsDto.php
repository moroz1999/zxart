<?php

declare(strict_types=1);

namespace ZxArt\Users\Dto;

/**
 * The fields the management form edits; `null` leaves a field untouched.
 * The group list, when given, replaces the user's groups.
 */
final readonly class UserFieldsDto
{
    /**
     * @param list<int>|null $groupIds
     */
    public function __construct(
        public ?string $userName = null,
        public ?string $email = null,
        public ?bool $verified = null,
        public ?bool $commentsAllowed = null,
        public ?bool $showEmail = null,
        public ?bool $vip = null,
        public ?bool $supporter = null,
        public ?bool $volunteer = null,
        public ?array $groupIds = null,
    ) {
    }
}
