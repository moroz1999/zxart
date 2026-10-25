<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Changes the fields the command carries and leaves the others as they are.
 *
 * @psalm-api
 */
final readonly class UpdateAuthorAliasHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorAliasRepository $authorAliasRepository,
        private AuthorAliasPrivileges $authorAliasPrivileges,
        private AuthorAliasFieldsWriter $authorAliasFieldsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function handle(UpdateAuthorAliasCommand $command): AuthorAliasDataResultDto
    {
        $alias = $this->authorAliasRepository->get($command->aliasId);
        $this->authorAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($alias, $command): void {
            $this->authorAliasFieldsWriter->apply($alias, $command->fields);
            $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::PRIVILEGE);
        });

        return new AuthorAliasDataResultDto(id: $command->aliasId);
    }
}
