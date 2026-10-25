<?php

declare(strict_types=1);

namespace ZxArt\Groups\Services;

use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorCommand;
use ZxArt\Groups\UseCases\ConvertGroupToAuthorHandler;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupCommand;
use ZxArt\Groups\UseCases\DeleteGroupHandler;
use ZxArt\Groups\UseCases\DeleteGroupMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupMemberHandler;
use ZxArt\Groups\UseCases\JoinGroupsCommand;
use ZxArt\Groups\UseCases\JoinGroupsHandler;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Groups\UseCases\UpdateGroupCommand;
use ZxArt\Groups\UseCases\UpdateGroupHandler;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Shared\Dto\UploadedFileDto;

/**
 * Changes to a group requested through its data endpoint, each one a use case
 * in `ZxArt\Groups\UseCases`: it checks the privilege of the matching legacy action, runs in
 * one transaction and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class GroupDataService
{
    public function __construct(
        private CreateGroupHandler $createGroupHandler,
        private UpdateGroupHandler $updateGroupHandler,
        private DeleteGroupHandler $deleteGroupHandler,
        private ConvertGroupToAuthorHandler $convertGroupToAuthorHandler,
        private JoinGroupsHandler $joinGroupsHandler,
        private SetGroupMemberHandler $setGroupMemberHandler,
        private DeleteGroupMemberHandler $deleteGroupMemberHandler,
    ) {
    }

    /**
     * A list left `null` stores nothing.
     *
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     * @throws GroupDataException
     */
    public function create(
        GroupFieldsDto $fields,
        ?array $members = null,
        ?array $importOrigins = null,
        ?UploadedFileDto $image = null,
    ): GroupDataResultDto {
        return $this->createGroupHandler->handle(new CreateGroupCommand($fields, $members, $importOrigins, $image));
    }

    /**
     * Changes the fields the DTO carries and leaves the others as they are;
     * replaces each list given, and keeps a list left `null`.
     *
     * @param list<AuthorshipInputDto>|null $members
     * @param list<ImportOriginDto>|null $importOrigins
     * @throws GroupDataException
     */
    public function update(
        int $groupId,
        GroupFieldsDto $fields,
        ?array $members = null,
        ?array $importOrigins = null,
        ?UploadedFileDto $image = null,
        bool $removeImage = false,
    ): GroupDataResultDto {
        return $this->updateGroupHandler->handle(new UpdateGroupCommand($groupId, $fields, $members, $importOrigins, $image, $removeImage));
    }

    /**
     * @throws GroupDataException
     */
    public function delete(int $groupId): GroupDataResultDto
    {
        return $this->deleteGroupHandler->handle(new DeleteGroupCommand($groupId));
    }

    /**
     * @throws GroupDataException
     */
    public function convertToAuthor(int $groupId): GroupDataResultDto
    {
        return $this->convertGroupToAuthorHandler->handle(new ConvertGroupToAuthorCommand($groupId));
    }

    /**
     * @throws GroupDataException
     */
    public function join(int $groupId, int $joinedId, bool $asAlias = false): GroupDataResultDto
    {
        return $this->joinGroupsHandler->handle(new JoinGroupsCommand($groupId, $joinedId, $asAlias));
    }

    /**
     * @param list<string> $roles none stands for an unknown role
     * @throws GroupDataException
     */
    public function setMember(int $groupId, int $authorId, array $roles, ?int $startDate = null, ?int $endDate = null): GroupDataResultDto
    {
        return $this->setGroupMemberHandler->handle(new SetGroupMemberCommand($groupId, $authorId, $roles, $startDate, $endDate));
    }

    /**
     * @throws GroupDataException
     */
    public function deleteMember(int $groupId, int $authorId): GroupDataResultDto
    {
        return $this->deleteGroupMemberHandler->handle(new DeleteGroupMemberCommand($groupId, $authorId));
    }
}
