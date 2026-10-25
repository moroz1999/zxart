<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorFieldsWriter;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Changes the fields the command carries and leaves the others as they are.
 *
 * @psalm-api
 */
final readonly class UpdateAuthorHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorRepository $authorRepository,
        private AuthorPrivileges $authorPrivileges,
        private AuthorFieldsWriter $authorFieldsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(UpdateAuthorCommand $command): AuthorDataResultDto
    {
        $author = $this->authorRepository->get($command->authorId);
        $this->authorPrivileges->assert($command->authorId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($author, $command): void {
            $this->authorFieldsWriter->apply($author, $command->fields);
            $this->actionsLogService->log($author, StructureType::Author, self::PRIVILEGE);
        });

        return new AuthorDataResultDto(id: $command->authorId);
    }
}
