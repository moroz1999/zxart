<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * Reads one group alias.
 *
 * @psalm-api
 */
final readonly class GetGroupAliasAction implements ReadAction
{
    public function __construct(
        private GroupDtos $groupDtos,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'get_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Reads one group alias: its fields as update_group_alias takes them, its group, its members with roles and '
            . 'dates, and the counts of its works.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): GroupAliasDto
    {
        return $this->groupDtos->alias(IdArgumentsDto::from($arguments)->id);
    }
}
