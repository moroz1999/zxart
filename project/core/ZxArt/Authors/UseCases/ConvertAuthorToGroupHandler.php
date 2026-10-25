<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Turns the author into a new group; the author itself is deleted.
 *
 * @psalm-api
 */
final readonly class ConvertAuthorToGroupHandler
{
    private const string PRIVILEGE = 'convertToGroup';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorRepository $authorRepository,
        private AuthorPrivileges $authorPrivileges,
        private GroupsService $groupsService,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(ConvertAuthorToGroupCommand $command): AuthorDataResultDto
    {
        $author = $this->authorRepository->get($command->authorId);
        $this->authorPrivileges->assert($command->authorId, self::PRIVILEGE);

        $resultId = $this->transactionRunner->run(function () use ($author): int {
            $group = $this->groupsService->convertAuthorToGroup($author);
            if ($group === null) {
                throw new AuthorDataException('The group could not be created', 500);
            }
            $this->actionsLogService->log($author, StructureType::Author, self::PRIVILEGE);
            return $group->getId();
        });

        return new AuthorDataResultDto(id: $resultId);
    }
}
