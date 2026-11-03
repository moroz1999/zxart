<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * Creates an alias of an author.
 *
 * @psalm-api
 */
final readonly class CreateAuthorAliasAction implements WriteAction
{
    public function __construct(
        private CreateAuthorAliasHandler $createAuthorAliasHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates an alias of an author: another name the same person is known by.';
    }

    #[Override]
    public function schema(): string
    {
        return CreateAuthorAliasArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Create;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::AuthorAlias;
    }

    #[Override]
    public function state(Arguments $arguments): null
    {
        return null;
    }

    #[Override]
    public function write(Arguments $arguments): AuthorAliasDataResultDto
    {
        $argumentsDto = CreateAuthorAliasArgumentsDto::from($arguments);

        return $this->createAuthorAliasHandler->handle(new CreateAuthorAliasCommand(new AuthorAliasFieldsDto(
            title: $argumentsDto->title,
            authorId: $argumentsDto->authorId,
            startDate: $argumentsDto->startDate,
            endDate: $argumentsDto->endDate,
        )));
    }
}
