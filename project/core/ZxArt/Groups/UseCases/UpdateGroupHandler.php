<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Changes the fields the command carries and leaves the others as they are.
 *
 * @psalm-api
 */
final readonly class UpdateGroupHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private GroupFieldsWriter $groupFieldsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(UpdateGroupCommand $command): GroupDataResultDto
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($group, $command): void {
            $this->groupFieldsWriter->apply($group, $command->fields);
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
        });

        return new GroupDataResultDto(id: $command->groupId);
    }
}
