<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupAliasElement;

/**
 * @psalm-api
 */
final readonly class DeleteGroupAliasHandler
{
    private const string PRIVILEGE = 'publicDelete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(DeleteGroupAliasCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->check($command);

        $this->transactionRunner->run(function () use ($alias): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
            $this->groupAliasRepository->delete($alias);
        });

        return new GroupAliasDataResultDto(id: $command->aliasId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupAliasDataException
     */
    public function check(DeleteGroupAliasCommand $command): groupAliasElement
    {
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        return $alias;
    }
}
