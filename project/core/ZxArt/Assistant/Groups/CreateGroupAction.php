<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * Creates a group.
 *
 * @psalm-api
 */
final readonly class CreateGroupAction implements WriteAction
{
    public function __construct(
        private CreateGroupHandler $createGroupHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates a group. Add aliases with create_group_alias and members with set_group_member afterwards.';
    }

    #[Override]
    public function schema(): string
    {
        return CreateGroupArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Create;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): null
    {
        return null;
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = CreateGroupArgumentsDto::from($arguments);

        return $this->createGroupHandler->handle(new CreateGroupCommand(new GroupFieldsDto(
            title: $argumentsDto->title,
            abbreviation: $argumentsDto->abbreviation,
            type: $argumentsDto->type,
            countryId: $argumentsDto->countryId,
            cityId: $argumentsDto->cityId,
            website: $argumentsDto->website,
            wikiLink: $argumentsDto->wikiLink,
            slogan: $argumentsDto->slogan,
            startDate: $argumentsDto->startDate,
            endDate: $argumentsDto->endDate,
            subGroupIds: $argumentsDto->subGroupIds,
        )));
    }
}
