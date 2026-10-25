<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use authorAliasElement;
use authorElement;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Adds the author to the group's members, or makes their roles these.
 *
 * @psalm-api
 */
final readonly class SetGroupMemberHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private structureManager $structureManager,
        private AuthorshipRepository $authorshipRepository,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(SetGroupMemberCommand $command): GroupDataResultDto
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);
        $member = $command->authorId > 0 ? $this->structureManager->getElementById($command->authorId) : null;
        if (!$member instanceof authorElement && !$member instanceof authorAliasElement) {
            throw new GroupDataException('A member must be an author or an author alias', 400);
        }
        $unknownRoles = array_diff($command->roles, GroupMemberRoles::LIST);
        if ($unknownRoles !== []) {
            throw new GroupDataException('Unknown role: ' . implode(', ', $unknownRoles), 400);
        }

        $this->transactionRunner->run(function () use ($group, $command): void {
            $roles = $command->roles === [] ? ['unknown'] : $command->roles;
            $this->authorshipRepository->setAuthorship($command->groupId, $command->authorId, EntityType::Group, $roles, $command->startDate, $command->endDate);
            $this->groupRepository->touch($group);
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
        });

        return new GroupDataResultDto(id: $command->groupId);
    }
}
