<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Authors;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * Creates an author.
 *
 * @psalm-api
 */
final readonly class CreateAuthorAction implements WriteAction
{
    public function __construct(
        private CreateAuthorHandler $createAuthorHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates an author; it is placed under the letter of its title. Add aliases with create_author_alias and '
            . 'memberships with set_group_member afterwards.';
    }

    #[Override]
    public function schema(): string
    {
        return CreateAuthorArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Create;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Author;
    }

    #[Override]
    public function state(Arguments $arguments): null
    {
        return null;
    }

    #[Override]
    public function write(Arguments $arguments): AuthorDataResultDto
    {
        $argumentsDto = CreateAuthorArgumentsDto::from($arguments);

        return $this->createAuthorHandler->handle(new CreateAuthorCommand(new AuthorFieldsDto(
            title: $argumentsDto->title,
            realName: $argumentsDto->realName,
            countryId: $argumentsDto->countryId,
            cityId: $argumentsDto->cityId,
            wikiLink: $argumentsDto->wikiLink,
        )));
    }
}
