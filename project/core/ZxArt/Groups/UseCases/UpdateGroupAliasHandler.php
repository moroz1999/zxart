<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Repositories\GroupAliasRepository;
use ZxArt\Groups\Services\GroupAliasFieldsWriter;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupAliasElement;

/**
 * Changes the fields the command carries and leaves the others as they are;
 * replaces each list it carries (members, import origins). All in one
 * transaction: a wrong reference, role changes nothing.
 *
 * @psalm-api
 */
final readonly class UpdateGroupAliasHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasRepository $groupAliasRepository,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private GroupAliasFieldsWriter $groupAliasFieldsWriter,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(UpdateGroupAliasCommand $command): GroupAliasDataResultDto
    {
        $alias = $this->check($command);

        $this->transactionRunner->run(function () use ($alias, $command): void {
            try {
                $this->groupAliasFieldsWriter->apply($alias, $command->fields);
                if ($command->members !== null) {
                    $this->elementAuthorsWriter->replace($command->aliasId, EntityType::Group, $command->members);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($command->aliasId, EntityType::Group, $command->importOrigins);
                }
            } catch (InvalidReferenceException $exception) {
                throw new GroupAliasDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($alias, StructureType::GroupAlias, self::PRIVILEGE);
        });

        return new GroupAliasDataResultDto(id: $command->aliasId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupAliasDataException
     */
    public function check(UpdateGroupAliasCommand $command): groupAliasElement
    {
        $alias = $this->groupAliasRepository->get($command->aliasId);
        $this->groupAliasPrivileges->assert($command->aliasId, self::PRIVILEGE);

        return $alias;
    }
}
