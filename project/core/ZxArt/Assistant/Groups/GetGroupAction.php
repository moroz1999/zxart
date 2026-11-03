<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * Reads one group.
 *
 * @psalm-api
 */
final readonly class GetGroupAction implements ReadAction
{
    public function __construct(
        private GroupDtos $groupDtos,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'get_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Reads one group: its fields as update_group takes them, the country, city and subgroups, the aliases with '
            . 'their dates, the members with roles and dates, the groups it is a subgroup of, and the counts of its works.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): GroupDto
    {
        return $this->groupDtos->group(IdArgumentsDto::from($arguments)->id);
    }
}
