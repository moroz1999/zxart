<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupAliasElement;

/**
 * Turns the group alias into a new group; the group alias itself is deleted.
 *
 * @psalm-api
 */
final readonly class ConvertGroupAliasToGroupHandler
{
    private const string PRIVILEGE = 'convertToGroup';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private GroupsService $groupsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(ConvertGroupAliasToGroupCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->check($command);

        $resultId = $this->transactionRunner->run(function () use ($alias): int {
            $group = $this->groupsService->convertGroupAliasToGroup($alias);
            if ($group === null) {
                throw new GroupAliasDataException('The group could not be created', 500);
            }
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
            return $group->getId();
        });

        return new GroupAliasDataResultDto(id: $resultId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupAliasDataException
     */
    public function check(ConvertGroupAliasToGroupCommand $command): groupAliasElement
    {
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        return $alias;
    }
}
