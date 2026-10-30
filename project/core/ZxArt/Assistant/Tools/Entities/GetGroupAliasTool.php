<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

use Override;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Groups\Dto\GroupAliasCardDto;
use ZxArt\Groups\Services\GroupAliasCardReader;

/**
 * @implements ReadTool<GetEntityRequestDto>
 *
 * @psalm-api
 */
final readonly class GetGroupAliasTool implements ReadTool
{
    public function __construct(
        private GroupAliasCardReader $groupAliasCardReader,
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
        return 'Reads one group alias: every field as update_group_alias takes it, its group, its members with roles '
            . 'and dates, and the counts of its productions and releases.';
    }

    #[Override]
    public function requestClass(): string
    {
        return GetEntityRequestDto::class;
    }

    #[Override]
    public function execute(object $request): GroupAliasCardDto
    {
        return $this->groupAliasCardReader->read($request->id);
    }
}
