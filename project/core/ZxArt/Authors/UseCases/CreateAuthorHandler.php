<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorCreator;
use ZxArt\Authors\Services\AuthorMembershipsWriter;
use ZxArt\Authors\Services\AuthorPrivileges;
use ZxArt\Import\Services\ImportOriginsWriter;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Shared\Elements\ElementImageWriter;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * Creates the author with its fields and the lists the command carries (real
 * names per language, groups, productions, import origins), and its image, all
 * in one transaction: a wrong reference, role or image creates nothing.
 *
 * @psalm-api
 */
final readonly class CreateAuthorHandler
{
    /** Held on the root: adding one anywhere in the catalogue. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorPrivileges $authorPrivileges,
        private AuthorCreator $authorCreator,
        private AuthorRepository $authorRepository,
        private AuthorMembershipsWriter $authorMembershipsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ElementImageWriter $elementImageWriter,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(CreateAuthorCommand $command): AuthorDataResultDto
    {
        $this->check($command);

        $id = $this->transactionRunner->run(function () use ($command): int {
            try {
                $element = $this->authorCreator->create($command->fields, $command->realNames);
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
                if ($command->image !== null) {
                    $this->elementImageWriter->store($element, $command->image);
                    $this->authorRepository->save($element);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new AuthorDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($element, StructureType::Author, self::PRIVILEGE);
            return $elementId;
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new AuthorDataResultDto(id: $id);
    }

    /**
     * Everything {@see self::handle()} refuses, without writing.
     *
     * @throws AuthorDataException
     */
    public function check(CreateAuthorCommand $command): void
    {
        $this->authorPrivileges->assertMayCreate();
    }
}
