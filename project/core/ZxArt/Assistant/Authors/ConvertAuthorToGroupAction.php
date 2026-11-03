<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupCommand;
use ZxArt\Authors\UseCases\ConvertAuthorToGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * Turns an author into a group.
 *
 * @psalm-api
 */
final readonly class ConvertAuthorToGroupAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private ConvertAuthorToGroupHandler $convertAuthorToGroupHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'convert_author_to_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Turns an author that is really a group into a group of the same title: the productions it is credited on '
            . 'become the group\'s, its releases the group\'s publications; the author is deleted.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Convert;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Author;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->authorDtos->author($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->convertAuthorToGroupHandler->handle(new ConvertAuthorToGroupCommand($argumentsDto->id));
    }
}
