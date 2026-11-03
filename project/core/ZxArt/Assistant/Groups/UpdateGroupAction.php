<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\UseCases\UpdateGroupCommand;
use ZxArt\Groups\UseCases\UpdateGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * Changes the given fields of a group.
 *
 * @psalm-api
 */
final readonly class UpdateGroupAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private UpdateGroupHandler $updateGroupHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a group that are given and leaves the others; an empty string clears a text.';
    }

    #[Override]
    public function schema(): string
    {
        return UpdateGroupArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Update;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): GroupDto
    {
        $argumentsDto = UpdateGroupArgumentsDto::from($arguments);

        return $this->groupDtos->group($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = UpdateGroupArgumentsDto::from($arguments);

        return $this->updateGroupHandler->handle(new UpdateGroupCommand($argumentsDto->id, new GroupFieldsDto(
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
