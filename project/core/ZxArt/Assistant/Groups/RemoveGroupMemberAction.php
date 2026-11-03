<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\UseCases\DeleteGroupMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Removes a member from a group.
 *
 * @psalm-api
 */
final readonly class RemoveGroupMemberAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private DeleteGroupMemberHandler $deleteGroupMemberHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_group_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes an author or an author alias from the members of a group.';
    }

    #[Override]
    public function schema(): string
    {
        return RemoveGroupMemberArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Unlink;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): GroupDto
    {
        $argumentsDto = RemoveGroupMemberArgumentsDto::from($arguments);

        return $this->groupDtos->group($argumentsDto->groupId);
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = RemoveGroupMemberArgumentsDto::from($arguments);

        return $this->deleteGroupMemberHandler->handle(new DeleteGroupMemberCommand($argumentsDto->groupId, $argumentsDto->authorId));
    }
}
