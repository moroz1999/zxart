<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Groups;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\UseCases\JoinGroupsCommand;
use ZxArt\Groups\UseCases\JoinGroupsHandler;
use ZxArt\Shared\EntityType;

/**
 * Merges a duplicate group into the main one; the plan shows the duplicate.
 *
 * @psalm-api
 */
final readonly class JoinGroupsAction implements WriteAction
{
    public function __construct(
        private GroupDtos $groupDtos,
        private JoinGroupsHandler $joinGroupsHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_groups';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate group into the main one: its members, productions and aliases move, and the joined '
            . 'group is deleted; with asAlias they go to a new alias of the main group named as the joined one.';
    }

    #[Override]
    public function schema(): string
    {
        return JoinGroupsArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Join;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Group;
    }

    #[Override]
    public function state(Arguments $arguments): GroupDto
    {
        $argumentsDto = JoinGroupsArgumentsDto::from($arguments);

        return $this->groupDtos->group($argumentsDto->joinedId);
    }

    #[Override]
    public function write(Arguments $arguments): GroupDataResultDto
    {
        $argumentsDto = JoinGroupsArgumentsDto::from($arguments);

        return $this->joinGroupsHandler->handle(new JoinGroupsCommand($argumentsDto->mainId, $argumentsDto->joinedId, $argumentsDto->asAlias));
    }
}
