<?php

declare(strict_types=1);

namespace ZxArt\Authors\UseCases;

use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Exception\AuthorDataException;
use ZxArt\Authors\Repositories\AuthorRepository;
use ZxArt\Authors\Services\AuthorFieldsWriter;
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
 * Changes the fields the command carries and leaves the others as they are;
 * replaces each list it carries (real names per language, groups, productions,
 * import origins) and the image. All in one transaction: a wrong reference,
 * role or image changes nothing.
 *
 * @psalm-api
 */
final readonly class UpdateAuthorHandler
{
    private const string PRIVILEGE = 'publicReceive';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private AuthorRepository $authorRepository,
        private AuthorPrivileges $authorPrivileges,
        private AuthorFieldsWriter $authorFieldsWriter,
        private AuthorMembershipsWriter $authorMembershipsWriter,
        private ImportOriginsWriter $importOriginsWriter,
        private ElementImageWriter $elementImageWriter,
        private ActionsLogService $actionsLogService,
    ) {
    }

    /**
     * @throws AuthorDataException
     */
    public function handle(UpdateAuthorCommand $command): AuthorDataResultDto
    {
        $author = $this->authorRepository->get($command->authorId);
        $this->authorPrivileges->assert($command->authorId, self::PRIVILEGE);

        $this->transactionRunner->run(function () use ($author, $command): void {
            try {
                $this->authorFieldsWriter->apply($author, $command->fields, $command->realNames);
                if ($command->groups !== null) {
                    $this->authorMembershipsWriter->replace($command->authorId, EntityType::Group, $command->groups);
                }
                if ($command->prods !== null) {
                    $this->authorMembershipsWriter->replace($command->authorId, EntityType::Prod, $command->prods);
                }
                if ($command->importOrigins !== null) {
                    $this->importOriginsWriter->replace($command->authorId, EntityType::Author, $command->importOrigins);
                }
                if ($command->removeImage) {
                    $this->elementImageWriter->remove($author);
                }
                if ($command->image !== null) {
                    $this->elementImageWriter->store($author, $command->image);
                }
                if ($command->removeImage || $command->image !== null) {
                    $this->authorRepository->save($author);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new AuthorDataException($exception->getMessage(), 400, $exception);
            }
            $this->actionsLogService->log($author, StructureType::Author, self::PRIVILEGE);
        });

        return new AuthorDataResultDto(id: $command->authorId);
    }
}
