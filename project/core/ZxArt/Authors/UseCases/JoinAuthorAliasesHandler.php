<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Moves everything of the joined author alias into this one and deletes it.
 *
 * @psalm-api
 */
final readonly class JoinAuthorAliasesHandler
{
    private const string PRIVILEGE = 'join';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorAliasRepository $authorAliasRepository,
        private AuthorAliasPrivileges $authorAliasPrivileges,
        private AuthorsService $authorsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function handle(JoinAuthorAliasesCommand $command): AuthorAliasDataResultDto
    {
        if ($command->joinedId === $command->aliasId) {
            throw new AuthorAliasDataException('A author alias cannot be joined into itself', 400);
        }
        $alias = $this->authorAliasRepository->get($command->aliasId);
        $this->authorAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);
        $this->authorAliasRepository->get($command->joinedId);
        $this->authorAliasPrivileges->assert($command->joinedId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($alias, $command): void {
            $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::PRIVILEGE);
            $this->authorsService->joinDeleteAuthor($command->aliasId, $command->joinedId);
        });

        return new AuthorAliasDataResultDto(id: $command->aliasId);
    }
}
