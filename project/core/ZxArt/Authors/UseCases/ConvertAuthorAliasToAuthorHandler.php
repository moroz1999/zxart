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
 * Turns the author alias into a new author; the author alias itself is deleted.
 *
 * @psalm-api
 */
final readonly class ConvertAuthorAliasToAuthorHandler
{
    private const string PRIVILEGE = 'convertToAuthor';

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
    public function handle(ConvertAuthorAliasToAuthorCommand $command): AuthorAliasDataResultDto
    {
        $alias = $this->authorAliasRepository->get($command->aliasId);
        $this->authorAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        $resultId = $this->transactionRunner->run(function () use ($alias): int {
            $author = $this->authorsService->convertAliasToAuthor($alias);
            if ($author === null) {
                throw new AuthorAliasDataException('The author could not be created', 500);
            }
            $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::PRIVILEGE);
            return $author->getId();
        });

        return new AuthorAliasDataResultDto(id: $resultId);
    }
}
