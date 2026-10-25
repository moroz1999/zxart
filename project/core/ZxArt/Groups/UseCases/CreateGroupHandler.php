<?php

declare(strict_types=1);

namespace ZxArt\Groups\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Exception\GroupDataException;
use ZxArt\Groups\Repositories\GroupRepository;
use ZxArt\Groups\Services\GroupCreator;
use ZxArt\Groups\Services\GroupPrivileges;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Shared\Elements\ElementImageWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Creates the group with its fields and the lists the command carries
 * (members, import origins), and its image, all in one transaction: a wrong
 * reference, role or image creates nothing.
 *
 * @psalm-api
 */
final readonly class CreateGroupHandler
{
    /** Held on the root: adding one anywhere in the catalogue. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private GroupPrivileges $groupPrivileges,
        private GroupCreator $groupCreator,
        private GroupRepository $groupRepository,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ElementImageWriter $elementImageWriter,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws GroupDataException
     */
    public function handle(CreateGroupCommand $command): GroupDataResultDto
    {
        $this->groupPrivileges->assertMayCreate();

        $id = $this->transactionRunner->run(function () use ($command): int {
            try {
                $element = $this->groupCreator->create($command->fields);
                $elementId = $element->getId();
                if ($command->members !== null) {
                    $this->elementAuthorsWriter->replace($elementId, EntityType::Group, $command->members);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($elementId, EntityType::Group, $command->importOrigins);
                }
                if ($command->image !== null) {
                    $this->elementImageWriter->store($element, $command->image);
                    $this->groupRepository->save($element);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new GroupDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($element, StructureType::Group, self::PRIVILEGE);
            return $elementId;
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new GroupDataResultDto(id: $id);
    }
}
