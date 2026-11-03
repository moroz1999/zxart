<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\UseCases\JoinGroupAliasesCommand;
use ZxArt\Groups\UseCases\JoinGroupAliasesHandler;
use ZxArt\Shared\EntityType;

/**
 * Merges a duplicate group alias into the main one; the plan shows the duplicate.
 *
 * @psalm-api
 */
final readonly class JoinGroupAliasesAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private JoinGroupAliasesHandler $joinGroupAliasesHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_group_aliases';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate alias into the main one: its productions, publications and members move, and the '
            . 'joined alias is deleted.';
    }

    #[Override]
    public function schema(): string
    {
        return JoinGroupAliasesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Join;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::GroupAlias;
    }

    #[Override]
    public function state(Arguments $arguments): GroupAliasDto
    {
        $argumentsDto = JoinGroupAliasesArgumentsDto::from($arguments);

        return $this->groupDtos->alias($argumentsDto->joinedId);
    }

    #[Override]
    public function write(Arguments $arguments): GroupAliasDataResultDto
    {
        $argumentsDto = JoinGroupAliasesArgumentsDto::from($arguments);

        return $this->joinGroupAliasesHandler->handle(new JoinGroupAliasesCommand($argumentsDto->mainId, $argumentsDto->joinedId));
    }
}
