<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Removes a member from a group alias.
 *
 * @psalm-api
 */
final readonly class RemoveGroupAliasMemberAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private DeleteGroupAliasMemberHandler $deleteGroupAliasMemberHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_group_alias_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes an author or an author alias from the members of a group alias.';
    }

    #[Override]
    public function schema(): string
    {
        return RemoveGroupAliasMemberArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Unlink;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): GroupAliasDto
    {
        $argumentsDto = RemoveGroupAliasMemberArgumentsDto::from($arguments);

        return $this->groupDtos->alias($argumentsDto->aliasId);
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = RemoveGroupAliasMemberArgumentsDto::from($arguments);

        return $this->deleteGroupAliasMemberHandler->handle(new DeleteGroupAliasMemberCommand($argumentsDto->aliasId, $argumentsDto->authorId));
    }
}
