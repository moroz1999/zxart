<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Services\GroupAliasCreator;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class CreateGroupAliasHandler
{
    /** Held on the group: adding an alias to it. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private GroupAliasCreator $groupAliasCreator,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(CreateGroupAliasCommand $command): GroupAliasDataResultDto
    {
        $ownerId = $command->fields->groupId;
        if ($ownerId === null || $ownerId <= 0) {
            throw new GroupAliasDataException('An alias needs its group', 400);
        }
        $this->groupAliasPrivileges->assertMayAddTo($ownerId);

        $id = $this->transactionRunner->run(function () use ($command): int {
            $element = $this->groupAliasCreator->create($command->fields);
            $this->actionsLogService->log($element, StructureType::GroupAlias, self::PRIVILEGE);
            return $element->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new GroupAliasDataResultDto(id: $id);
    }
}
