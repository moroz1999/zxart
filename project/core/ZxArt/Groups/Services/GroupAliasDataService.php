<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupCommand;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupHandler;
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasHandler;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupAliasesCommand;
use ZxArt\Groups\UseCases\JoinGroupAliasesHandler;
use ZxArt\Groups\UseCases\SetGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\SetGroupAliasMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupAliasCommand;
use ZxArt\Groups\UseCases\UpdateGroupAliasHandler;

/**
 * Changes to a group alias requested through its data endpoint, each one a use case
 * in `ZxArt\Groups\UseCases`: it checks the privilege of the matching legacy action, runs in
 * one transaction and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class GroupAliasDataService
{
    public function __construct(
        private CreateGroupAliasHandler $createGroupAliasHandler,
        private UpdateGroupAliasHandler $updateGroupAliasHandler,
        private DeleteGroupAliasHandler $deleteGroupAliasHandler,
        private ConvertGroupAliasToGroupHandler $convertGroupAliasToGroupHandler,
        private JoinGroupAliasesHandler $joinGroupAliasesHandler,
        private SetGroupAliasMemberHandler $setGroupAliasMemberHandler,
        private DeleteGroupAliasMemberHandler $deleteGroupAliasMemberHandler,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function create(GroupAliasFieldsDto $fields): GroupAliasDataResultDto
    {
        return $this->createGroupAliasHandler->handle(new CreateGroupAliasCommand($fields));
    }

    /**
     * Changes the fields the DTO carries and leaves the others as they are.
     *
     * @throws GroupAliasDataException
     */
    public function update(int $aliasId, GroupAliasFieldsDto $fields): GroupAliasDataResultDto
    {
        return $this->updateGroupAliasHandler->handle(new UpdateGroupAliasCommand($aliasId, $fields));
    }

    /**
     * @throws GroupAliasDataException
     */
    public function delete(int $aliasId): GroupAliasDataResultDto
    {
        return $this->deleteGroupAliasHandler->handle(new DeleteGroupAliasCommand($aliasId));
    }

    /**
     * @throws GroupAliasDataException
     */
    public function convertToGroup(int $aliasId): GroupAliasDataResultDto
    {
        return $this->convertGroupAliasToGroupHandler->handle(new ConvertGroupAliasToGroupCommand($aliasId));
    }

    /**
     * @throws GroupAliasDataException
     */
    public function join(int $aliasId, int $joinedId): GroupAliasDataResultDto
    {
        return $this->joinGroupAliasesHandler->handle(new JoinGroupAliasesCommand($aliasId, $joinedId));
    }

    /**
     * @param list<string> $roles none stands for an unknown role
     * @throws GroupAliasDataException
     */
    public function setMember(int $aliasId, int $authorId, array $roles, ?int $startDate = null, ?int $endDate = null): GroupAliasDataResultDto
    {
        return $this->setGroupAliasMemberHandler->handle(new SetGroupAliasMemberCommand($aliasId, $authorId, $roles, $startDate, $endDate));
    }

    /**
     * @throws GroupAliasDataException
     */
    public function deleteMember(int $aliasId, int $authorId): GroupAliasDataResultDto
    {
        return $this->deleteGroupAliasMemberHandler->handle(new DeleteGroupAliasMemberCommand($aliasId, $authorId));
    }
}
