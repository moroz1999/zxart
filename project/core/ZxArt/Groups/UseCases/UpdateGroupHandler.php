<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Shared\Elements\ElementImageWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;
use groupElement;

/**
 * Changes the fields the command carries and leaves the others as they are;
 * replaces each list it carries (members, import origins) and the image. All
 * in one transaction: a wrong reference, role or image changes nothing.
 *
 * @psalm-api
 */
final readonly class UpdateGroupHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupRepository $groupRepository,
        private GroupPrivileges $groupPrivileges,
        private GroupFieldsWriter $groupFieldsWriter,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ElementImageWriter $elementImageWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(UpdateGroupCommand $command): GroupDataResultDto
    {
        $group = $this->check($command);

        $this->transactionRunner->run(function () use ($group, $command): void {
            try {
                $this->groupFieldsWriter->apply($group, $command->fields);
                if ($command->members !== null) {
                    $this->elementAuthorsWriter->replace($command->groupId, EntityType::Group, $command->members);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($command->groupId, EntityType::Group, $command->importOrigins);
                }
                if ($command->removeImage) {
                    $this->elementImageWriter->remove($group);
                }
                if ($command->image !== null) {
                    $this->elementImageWriter->store($group, $command->image);
                }
                if ($command->removeImage || $command->image !== null) {
                    $this->groupRepository->save($group);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new GroupDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($group, StructureType::Group, self::PRIVILEGE);
        });

        return new GroupDataResultDto(id: $command->groupId);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws GroupDataException
     */
    public function check(UpdateGroupCommand $command): groupElement
    {
        $group = $this->groupRepository->get($command->groupId);
        $this->groupPrivileges->assert($command->groupId, self::PRIVILEGE);

        return $group;
    }
}
