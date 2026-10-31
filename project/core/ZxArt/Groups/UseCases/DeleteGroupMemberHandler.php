<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipDbal;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupElement;

/**
 * @psalm-api
 */
final readonly class DeleteGroupMemberHandler
{
    private const string PRIVILEGE = 'deleteAuthor';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private AuthorshipDbal $authorshipDbal,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(DeleteGroupMemberCommand $command): GroupDataResultDto
    {
        $group = $this->check($command);

        $this->transactionRunner->run(function () use ($group, $command): void {
            $this->authorshipDbal->deleteAuthorship($command->groupId, $command->authorId, EntityType::Group);
            $this->groupRepository->touch($group);
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
        });

        return new GroupDataResultDto(id: $command->groupId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupDataException
     */
    public function check(DeleteGroupMemberCommand $command): groupElement
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);
        if ($command->authorId <= 0) {
            throw new GroupDataException('Missing required author id', 400);
        }
        foreach ($this->authorshipDbal->findByElement($command->groupId, EntityType::Group) as $authorship) {
            if ($authorship->authorId === $command->authorId) {
                return $group;
            }
        }

        throw new GroupDataException('The author is not a member of this group', 404);
    }
}
