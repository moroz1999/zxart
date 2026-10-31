<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Services\AuthorAliasCreator;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Authors\Services\AuthorMembershipsWriter;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Creates the alias with its fields and the lists the command carries (groups,
 * productions, import origins), all in one transaction: a wrong reference,
 * role creates nothing.
 *
 * @psalm-api
 */
final readonly class CreateAuthorAliasHandler
{
    /** Held on the owner: adding an alias to it. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorAliasPrivileges $authorAliasPrivileges,
        private AuthorAliasCreator $authorAliasCreator,
        private AuthorMembershipsWriter $authorMembershipsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function handle(CreateAuthorAliasCommand $command): AuthorAliasDataResultDto
    {
        $this->check($command);

        $id = $this->transactionRunner->run(function () use ($command): int {
            try {
                $element = $this->authorAliasCreator->create($command->fields);
                $elementId = $element->getId();
                if ($command->groups !== null) {
                    $this->authorMembershipsWriter->replace($elementId, EntityType::Group, $command->groups);
                }
                if ($command->prods !== null) {
                    $this->authorMembershipsWriter->replace($elementId, EntityType::Prod, $command->prods);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($elementId, EntityType::Author, $command->importOrigins);
                }
            } catch (InvalidReferenceException $exception) {
                throw new AuthorAliasDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($element, StructureType::AuthorAlias, self::PRIVILEGE);
            return $elementId;
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new AuthorAliasDataResultDto(id: $id);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws AuthorAliasDataException
     */
    public function check(CreateAuthorAliasCommand $command): void
    {
        $ownerId = $command->fields->authorId;
        if ($ownerId === null || $ownerId <= 0) {
            throw new AuthorAliasDataException('An alias needs its author', 400);
        }
        $this->authorAliasPrivileges->assertMayAddTo($ownerId);
    }
}
