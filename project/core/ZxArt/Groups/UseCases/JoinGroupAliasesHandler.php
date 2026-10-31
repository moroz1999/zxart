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
 * Moves everything of the joined group alias into this one and deletes it.
 *
 * @psalm-api
 */
final readonly class JoinGroupAliasesHandler
{
    private const string PRIVILEGE = 'join';

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
    public function handle(JoinGroupAliasesCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->check($command);

        $this->transactionRunner->run(function () use ($alias, $command): void {
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
            $this->groupsService->joinDeleteGroup($command->aliasId, $command->joinedId);
        });

        return new GroupAliasDataResultDto(id: $command->aliasId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupAliasDataException
     */
    public function check(JoinGroupAliasesCommand $command): groupAliasElement
    {
        if ($command->joinedId === $command->aliasId) {
            throw new GroupAliasDataException('A group alias cannot be joined into itself', 400);
        }
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);
        $this->groupAliasRepository->get($command->joinedId);
        $this->groupAliasPrivileges->assert($command->joinedId, self::PRIVILEGE);

        return $alias;
    }
}
