<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\UseCases\SetGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\SetGroupAliasMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Makes an author a member of a group alias, or changes the membership.
 *
 * @psalm-api
 */
final readonly class SetGroupAliasMemberAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private SetGroupAliasMemberHandler $setGroupAliasMemberHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_group_alias_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Makes an author or an author alias a member of a group alias with the roles and dates given, or changes '
            . 'the roles and dates of a member.';
    }

    #[Override]
    public function schema(): string
    {
        return SetGroupAliasMemberArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Link;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): GroupAliasDto
    {
        $argumentsDto = SetGroupAliasMemberArgumentsDto::from($arguments);

        return $this->groupDtos->alias($argumentsDto->aliasId);
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = SetGroupAliasMemberArgumentsDto::from($arguments);

        return $this->setGroupAliasMemberHandler->handle(new SetGroupAliasMemberCommand(
            $argumentsDto->aliasId,
            $argumentsDto->authorId,
            $argumentsDto->roles,
            $argumentsDto->startDate,
            $argumentsDto->endDate,
        ));
    }
}
