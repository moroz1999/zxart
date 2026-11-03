<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\UseCases\DeleteAuthorCommand;
use ZxArt\Authors\UseCases\DeleteAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes an author.
 *
 * @psalm-api
 */
final readonly class DeleteAuthorAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private DeleteAuthorHandler $deleteAuthorHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes an author.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Delete;
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

        return $this->deleteAuthorHandler->handle(new DeleteAuthorCommand($argumentsDto->id));
    }
}
