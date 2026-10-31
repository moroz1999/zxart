<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

use Override;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Authors\Dto\AuthorCardDto;
use ZxArt\Authors\Services\AuthorCardReader;

/**
 * @implements ReadTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class GetAuthorTool implements ReadTool
{
    public function __construct(
        private AuthorCardReader $authorCardReader,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'get_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Reads one author: every field as update_author takes it, the country and city, the aliases with '
            . 'their dates, the group memberships with roles and dates, and the counts of productions, pictures, '
            . 'tunes and comments.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function execute(object $request): AuthorCardDto
    {
        return $this->authorCardReader->read($request->id);
    }
}
