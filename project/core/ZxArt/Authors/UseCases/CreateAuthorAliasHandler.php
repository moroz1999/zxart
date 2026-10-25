<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Services\AuthorAliasCreator;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * @psalm-api
 */
final readonly class CreateAuthorAliasHandler
{
    /** Held on the author: adding an alias to it. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorAliasPrivileges $authorAliasPrivileges,
        private AuthorAliasCreator $authorAliasCreator,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function handle(CreateAuthorAliasCommand $command): AuthorAliasDataResultDto
    {
        $ownerId = $command->fields->authorId;
        if ($ownerId === null || $ownerId <= 0) {
            throw new AuthorAliasDataException('An alias needs its author', 400);
        }
        $this->authorAliasPrivileges->assertMayAddTo($ownerId);

        $id = $this->transactionRunner->run(function () use ($command): int {
            $element = $this->authorAliasCreator->create($command->fields);
            $this->actionsLogService->log($element, StructureType::AuthorAlias, self::PRIVILEGE);
            return $element->getId();
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new AuthorAliasDataResultDto(id: $id);
    }
}
