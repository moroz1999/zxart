<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupElement;

/**
 * @psalm-api
 */
final readonly class DeleteGroupHandler
{
    private const string PRIVILEGE = 'publicDelete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(DeleteGroupCommand $command): GroupDataResultDto
    {
        $group = $this->check($command);

        $this->transactionRunner->run(function () use ($group): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
            $this->groupRepository->delete($group);
        });

        return new GroupDataResultDto(id: $command->groupId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupDataException
     */
    public function check(DeleteGroupCommand $command): groupElement
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);

        return $group;
    }
}
