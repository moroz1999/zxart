<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * Reads one author.
 *
 * @psalm-api
 */
final readonly class GetAuthorAction implements ReadAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
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
        return 'Reads one author: its fields as update_author takes them, the country and city, the aliases with their '
            . 'dates, the group memberships with roles and dates, and the counts of its works.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): AuthorDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->authorDtos->author($argumentsDto->id);
    }
}
