<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Services\GroupCreator;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class CreateGroupHandler
{
    /** Held on the root: adding a group anywhere in the catalogue. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupPrivileges $groupPrivileges,
        private GroupCreator $groupCreator,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(CreateGroupCommand $command): GroupDataResultDto
    {
        $this->groupPrivileges->assertMayCreate();

        $id = $this->transactionRunner->run(function () use ($command): int {
            $element = $this->groupCreator->create($command->fields);
            $this->actionsLogService->log($element, StructureType::Group, self::PRIVILEGE);
            return $element->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new GroupDataResultDto(id: $id);
    }
}
