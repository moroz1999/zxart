<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use authorElement;

/**
 * @psalm-api
 */
final readonly class DeleteAuthorHandler
{
    private const string PRIVILEGE = 'publicDelete';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorRepository $authorRepository,
        private AuthorPrivileges $authorPrivileges,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(DeleteAuthorCommand $command): AuthorDataResultDto
    {
        $author = $this->check($command);

        $this->transactionRunner->run(function () use ($author): void {
            // logged first: the element data is gone after the deletion
            $this->actionsLogService->log($author, StructureType::Author, self::PRIVILEGE);
            $this->authorRepository->delete($author);
        });

        return new AuthorDataResultDto(id: $command->authorId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws AuthorDataException
     */
    public function check(DeleteAuthorCommand $command): authorElement
    {
        $author = $this->authorRepository->get($command->authorId);
        $this->authorPrivileges->assert($command->authorId, self::PRIVILEGE);

        return $author;
    }
}
