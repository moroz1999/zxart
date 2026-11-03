<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\UseCases\UpdateGroupAliasCommand;
use ZxArt\Groups\UseCases\UpdateGroupAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * Changes the given fields of a group alias.
 *
 * @psalm-api
 */
final readonly class UpdateGroupAliasAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private UpdateGroupAliasHandler $updateGroupAliasHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a group alias that are given and leaves the others.';
    }

    #[Override]
    public function schema(): string
    {
        return UpdateGroupAliasArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): GroupAliasDto
    {
        $argumentsDto = UpdateGroupAliasArgumentsDto::from($arguments);

        return $this->groupDtos->alias($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = UpdateGroupAliasArgumentsDto::from($arguments);

        return $this->updateGroupAliasHandler->handle(new UpdateGroupAliasCommand($argumentsDto->id, new GroupAliasFieldsDto(
            title: $argumentsDto->title,
            groupId: $argumentsDto->groupId,
            startDate: $argumentsDto->startDate,
            endDate: $argumentsDto->endDate,
        )));
    }
}
