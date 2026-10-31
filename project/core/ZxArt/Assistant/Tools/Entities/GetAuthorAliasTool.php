<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

use Override;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Authors\Dto\AuthorAliasCardDto;
use ZxArt\Authors\Services\AuthorAliasCardReader;

/**
 * @implements ReadTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class GetAuthorAliasTool implements ReadTool
{
    public function __construct(
        private AuthorAliasCardReader $authorAliasCardReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'get_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Reads one author alias: every field as update_author_alias takes it, its author, its group '
            . 'memberships with roles and dates, and the counts of its works.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function execute(object $request): AuthorAliasCardDto
    {
        return $this->authorAliasCardReader->read($request->id);
    }
}
