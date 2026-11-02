<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Exception\AuthorAliasDataException;
use ZxArt\Authors\Repositories\AuthorAliasRepository;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Authors\Services\AuthorAliasPrivileges;
use ZxArt\Authors\Services\AuthorMembershipsWriter;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Changes the fields the command carries and leaves the others as they are;
 * replaces each list it carries (groups, productions, import origins). All in
 * one transaction: a wrong reference, role changes nothing.
 *
 * @psalm-api
 */
final readonly class UpdateAuthorAliasHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorAliasRepository $authorAliasRepository,
        private AuthorAliasPrivileges $authorAliasPrivileges,
        private AuthorAliasFieldsWriter $authorAliasFieldsWriter,
        private AuthorMembershipsWriter $authorMembershipsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorAliasDataException
     */
    public function handle(UpdateAuthorAliasCommand $command): AuthorAliasDataResultDto
    {
        $alias = $this->authorAliasRepository->get($command->aliasId);
        $this->authorAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($alias, $command): void {
            try {
                $this->authorAliasFieldsWriter->apply($alias, $command->fields);
                if ($command->groups !== null) {
                    $this->authorMembershipsWriter->replace($command->aliasId, EntityType::Group, $command->groups);
                }
                if ($command->prods !== null) {
                    $this->authorMembershipsWriter->replace($command->aliasId, EntityType::Prod, $command->prods);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($command->aliasId, EntityType::Author, $command->importOrigins);
                }
            } catch (InvalidReferenceException $exception) {
                throw new AuthorAliasDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($alias, StructureType::AuthorAlias, self::PRIVILEGE);
        });

        return new AuthorAliasDataResultDto(id: $command->aliasId);
    }
}
