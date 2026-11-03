<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Makes an author a member of a group, or changes the membership.
 *
 * @psalm-api
 */
final readonly class SetGroupMemberAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private SetGroupMemberHandler $setGroupMemberHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_group_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Makes an author or an author alias a member of a group with the roles and dates given, or changes the '
            . 'roles and dates of a member.';
    }

    #[Override]
    public function schema(): string
    {
        return SetGroupMemberArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Link;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): GroupDto
    {
        $argumentsDto = SetGroupMemberArgumentsDto::from($arguments);

        return $this->groupDtos->group($argumentsDto->groupId);
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = SetGroupMemberArgumentsDto::from($arguments);

        return $this->setGroupMemberHandler->handle(new SetGroupMemberCommand(
            $argumentsDto->groupId,
            $argumentsDto->authorId,
            $argumentsDto->roles,
            $argumentsDto->startDate,
            $argumentsDto->endDate,
        ));
    }
}
