<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\UpdateAuthorCommand;
use ZxArt\Authors\UseCases\UpdateAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * Changes the given fields of an author.
 *
 * @psalm-api
 */
final readonly class UpdateAuthorAction implements WriteAction
{
    public function __construct(
        private AuthorDtos $authorDtos,
        private UpdateAuthorHandler $updateAuthorHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of an author that are given and leaves the others; an empty string clears a text.';
    }

    #[Override]
    public function schema(): string
    {
        return UpdateAuthorArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Author;
    }

    #[Override]
    public function state(Arguments $arguments): AuthorDto
    {
        $argumentsDto = UpdateAuthorArgumentsDto::from($arguments);

        return $this->authorDtos->author($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): AuthorDataResultDto
    {
        $argumentsDto = UpdateAuthorArgumentsDto::from($arguments);

        return $this->updateAuthorHandler->handle(new UpdateAuthorCommand($argumentsDto->id, new AuthorFieldsDto(
            title: $argumentsDto->title,
            realName: $argumentsDto->realName,
            countryId: $argumentsDto->countryId,
            cityId: $argumentsDto->cityId,
            wikiLink: $argumentsDto->wikiLink,
        )));
    }
}
