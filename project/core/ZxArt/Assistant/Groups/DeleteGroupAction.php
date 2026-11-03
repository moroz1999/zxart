<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\UseCases\DeleteGroupCommand;
use ZxArt\Groups\UseCases\DeleteGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes a group.
 *
 * @psalm-api
 */
final readonly class DeleteGroupAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private DeleteGroupHandler $deleteGroupHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a group.';
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
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): GroupDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->groupDtos->group($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->deleteGroupHandler->handle(new DeleteGroupCommand($argumentsDto->id));
    }
}
