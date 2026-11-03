<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * Creates an alias of a group.
 *
 * @psalm-api
 */
final readonly class CreateGroupAliasAction implements WriteAction
{
    public function __construct(
        private CreateGroupAliasHandler $createGroupAliasHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates an alias of a group: another name it is known by.';
    }

    #[Override]
    public function schema(): string
    {
        return CreateGroupAliasArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Create;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): null
    {
        return null;
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = CreateGroupAliasArgumentsDto::from($arguments);

        return $this->createGroupAliasHandler->handle(new CreateGroupAliasCommand(new GroupAliasFieldsDto(
            title: $argumentsDto->title,
            groupId: $argumentsDto->groupId,
            startDate: $argumentsDto->startDate,
            endDate: $argumentsDto->endDate,
        )));
    }
}
