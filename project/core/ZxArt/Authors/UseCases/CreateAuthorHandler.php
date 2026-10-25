<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Services\AuthorCreator;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class CreateAuthorHandler
{
    /** Held on the root: adding an author anywhere in the catalogue. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorPrivileges $authorPrivileges,
        private AuthorCreator $authorCreator,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(CreateAuthorCommand $command): AuthorDataResultDto
    {
        $this->authorPrivileges->assertMayCreate();

        $id = $this->transactionRunner->run(function () use ($command): int {
            $element = $this->authorCreator->create($command->fields);
            $this->actionsLogService->log($element, StructureType::Author, self::PRIVILEGE);
            return $element->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new AuthorDataResultDto(id: $id);
    }
}
