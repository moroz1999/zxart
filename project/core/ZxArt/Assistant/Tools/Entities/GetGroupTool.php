<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Groups\Dto\GroupCardDto;
use ZxArt\Groups\Services\GroupCardReader;

/**
 * @implements ReadTool<GetEntityRequestDto>
 *
 * @psalm-api
 */
final readonly class GetGroupTool implements ReadTool
{
    public function __construct(
        private GroupCardReader $groupCardReader,
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
        return 'Reads one group: every field as update_group takes it, the titles of the entities the fields name by '
            . 'id, the members with roles and dates, the groups it is a subgroup of, the aliases with their dates, '
            . 'and the counts of members, subgroups, productions and published releases.';
    }

    #[Override]
    public function requestClass(): string
    {
        return GetEntityRequestDto::class;
    }

    #[Override]
    public function execute(object $request): GroupCardDto
    {
        return $this->groupCardReader->read($request->id);
    }
}
