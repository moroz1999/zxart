<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\UseCases\UpdateAuthorAliasCommand;
use ZxArt\Authors\UseCases\UpdateAuthorAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * Changes the given fields of an author alias.
 *
 * @psalm-api
 */
final readonly class UpdateAuthorAliasAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private UpdateAuthorAliasHandler $updateAuthorAliasHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of an author alias that are given and leaves the others.';
    }

    #[Override]
    public function schema(): string
    {
        return UpdateAuthorAliasArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::AuthorAlias;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorAliasDto
    {
        $argumentsDto = UpdateAuthorAliasArgumentsDto::from($arguments);

        return $this->authorDtos->alias($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorAliasDataResultDto
    {
        $argumentsDto = UpdateAuthorAliasArgumentsDto::from($arguments);

        return $this->updateAuthorAliasHandler->handle(new UpdateAuthorAliasCommand($argumentsDto->id, new AuthorAliasFieldsDto(
            title: $argumentsDto->title,
            authorId: $argumentsDto->authorId,
            startDate: $argumentsDto->startDate,
            endDate: $argumentsDto->endDate,
        )));
    }
}
