<?php

declare(strict_types=1);

namespace ZxArt\Groups\Dto;

/**
 * Editable fields of a group alias. `null` leaves a field as it is; dates are
 * timestamps, 0 clearing them.
 */
final readonly class GroupAliasFieldsDto
{
    /**
     * @param int|null $groupId the group the alias belongs to; another one moves it
     */
    public function __construct(
        public ?string $title = null,
        public ?int $groupId = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
    ) {
    }
}
