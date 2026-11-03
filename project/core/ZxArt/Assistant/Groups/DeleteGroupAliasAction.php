<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\UseCases\DeleteGroupAliasCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes a group alias.
 *
 * @psalm-api
 */
final readonly class DeleteGroupAliasAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private DeleteGroupAliasHandler $deleteGroupAliasHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a group alias.';
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
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): GroupAliasDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->groupDtos->alias($argumentsDto->id);
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = IdArgumentsDto::from($arguments);

        return $this->deleteGroupAliasHandler->handle(new DeleteGroupAliasCommand($argumentsDto->id));
    }
}
