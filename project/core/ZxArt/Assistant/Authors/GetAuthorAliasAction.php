<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * Reads one author alias.
 *
 * @psalm-api
 */
final readonly class GetAuthorAliasAction implements ReadAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
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
        return 'Reads one author alias: its fields as update_author_alias takes them, its author, its group memberships '
            . 'with roles and dates, and the counts of its works.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): AuthorAliasDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->authorDtos->alias($argumentsDto->id);
    }
}
