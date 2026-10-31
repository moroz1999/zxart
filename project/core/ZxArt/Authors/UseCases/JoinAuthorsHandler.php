<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorElement;

/**
 * Moves everything of the joined author into this one. The joined author is
 * deleted, or with `asAlias` kept as an alias of this one.
 *
 * @psalm-api
 */
final readonly class JoinAuthorsHandler
{
    private const string PRIVILEGE = 'join';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorRepository $authorRepository,
        private AuthorPrivileges $authorPrivileges,
        private AuthorsService $authorsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(JoinAuthorsCommand $command): AuthorDataResultDto
    {
        $author = $this->check($command);

        $this->transactionRunner->run(function () use ($author, $command): void {
            $this->actionsLogService->log($author, StructureType::Author, self::PRIVILEGE);
            if ($command->asAlias) {
                $this->authorsService->joinAuthorAsAlias($command->authorId, $command->joinedId);
            } else {
                $this->authorsService->joinDeleteAuthor($command->authorId, $command->joinedId);
            }
            $author->recalculate();
        });

        return new AuthorDataResultDto(id: $command->authorId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws AuthorDataException
     */
    public function check(JoinAuthorsCommand $command): authorElement
    {
        if ($command->joinedId === $command->authorId) {
            throw new AuthorDataException('A author cannot be joined into itself', 400);
        }
        $author = $this->authorRepository->get($command->authorId);
        $this->authorPrivileges->assert($command->authorId, self::PRIVILEGE);
        $this->authorRepository->get($command->joinedId);
        $this->authorPrivileges->assert($command->joinedId, self::PRIVILEGE);

        return $author;
    }
}
