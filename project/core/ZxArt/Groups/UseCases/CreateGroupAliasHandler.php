<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Exception\GroupAliasDataException;
use ZxArt\Groups\Services\GroupAliasCreator;
use ZxArt\Groups\Services\GroupAliasPrivileges;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Creates the alias with its fields and the lists the command carries
 * (members, import origins), all in one transaction: a wrong reference, role
 * creates nothing.
 *
 * @psalm-api
 */
final readonly class CreateGroupAliasHandler
{
    /** Held on the owner: adding an alias to it. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupAliasPrivileges $groupAliasPrivileges,
        private GroupAliasCreator $groupAliasCreator,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws GroupAliasDataException
     */
    public function handle(CreateGroupAliasCommand $command): GroupAliasDataResultDto
    {
        $this->check($command);

        $id = $this->transactionRunner->run(function () use ($command): int {
            try {
                $element = $this->groupAliasCreator->create($command->fields);
                $elementId = $element->getId();
                if ($command->members !== null) {
                    $this->elementAuthorsWriter->replace($elementId, EntityType::Group, $command->members);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($elementId, EntityType::Group, $command->importOrigins);
                }
            } catch (InvalidReferenceException $exception) {
                throw new GroupAliasDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($element, StructureType::GroupAlias, self::PRIVILEGE);
            return $elementId;
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new GroupAliasDataResultDto(id: $id);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupAliasDataException
     */
    public function check(CreateGroupAliasCommand $command): void
    {
        $ownerId = $command->fields->groupId;
        if ($ownerId === null || $ownerId <= 0) {
            throw new GroupAliasDataException('An alias needs its group', 400);
        }
        $this->groupAliasPrivileges->assertMayAddTo($ownerId);
    }
}
