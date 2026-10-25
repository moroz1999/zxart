<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class DeleteAuthorAliasHandler
{
    private const string PRIVILEGE = 'publicDelete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorAliasRepository $authorAliasRepository,
        private AuthorAliasPrivileges $authorAliasPrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function handle(DeleteAuthorAliasCommand $command): AuthorAliasDataResultDto
    {
        $alias = $this->authorAliasRepository->get($command->aliasId);
        $this->authorAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($alias): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::PRIVILEGE);
            $this->authorAliasRepository->delete($alias);
        });

        return new AuthorAliasDataResultDto(id: $command->aliasId);
    }
}
