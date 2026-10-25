<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasFieldsWriter;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Changes the fields the command carries and leaves the others as they are.
 *
 * @psalm-api
 */
final readonly class UpdateGroupAliasHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private GroupAliasFieldsWriter $groupAliasFieldsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(UpdateGroupAliasCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($alias, $command): void {
            $this->groupAliasFieldsWriter->apply($alias, $command->fields);
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
        });

        return new GroupAliasDataResultDto(id: $command->aliasId);
    }
}
