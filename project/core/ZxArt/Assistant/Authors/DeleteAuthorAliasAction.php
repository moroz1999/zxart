<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\UseCases\DeleteAuthorAliasCommand;
use ZxArt\Authors\UseCases\DeleteAuthorAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes an author alias.
 *
 * @psalm-api
 */
final readonly class DeleteAuthorAliasAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private DeleteAuthorAliasHandler $deleteAuthorAliasHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes an author alias.';
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
        return EntityType::AuthorAlias;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorAliasDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->authorDtos->alias($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorAliasDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->deleteAuthorAliasHandler->handle(new DeleteAuthorAliasCommand($argumentsDto->id));
    }
}
