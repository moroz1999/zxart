<?php

declare(strict_types=1);

namespace ZxArt\Prods\UseCases;

use App\Users\CurrentUserService;
use structureManager;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\Services\ProdCreator;
use ZxArt\Prods\Services\ProdPrivileges;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Shared\Dto\CreatedIdsDto;
use ZxArt\Shared\Dto\UploadedFileDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * The production upload: a production per uploaded software file, each with
 * the values typed once — the authors and the screenshots, maps and other
 * files among them — and one release holding its file. A production is titled
 * as given, or after its file: the name without the extension, underscores as
 * spaces, the first letter capital; its release takes the same title.
 *
 * All in one transaction: a wrong reference, role or file creates nothing.
 *
 * @psalm-api
 */
final readonly class BatchUploadProdsHandler
{
    /** Held on the root: the privilege that opens the production upload form. */
    private const string PRIVILEGE = 'batchUpload';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private structureManager $structureManager,
        private ProdPrivileges $prodPrivileges,
        private ProdCreator $prodCreator,
        private ReleaseCreator $releaseCreator,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ElementFileImporter $elementFileImporter,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ProdDataException
     */
    public function handle(BatchUploadProdsCommand $command): CreatedIdsDto
    {
        if ($command->releaseFiles === []) {
            throw new ProdDataException('At least one software file is required', 400);
        }
        // the privileges of an element are worked out as it loads
        $this->structureManager->getRootElement();
        $this->prodPrivileges->assert($this->structureManager->getRootElementId(), self::PRIVILEGE, StructureType::ZxProdsUploadForm);

        $prodIds = $this->transactionRunner->run(function () use ($command): array {
            try {
                foreach ($command->files as $upload) {
                    $this->elementFileImporter->checkUpload($upload->kind, $upload->file);
                }
                foreach ($command->releaseFiles as $releaseFile) {
                    $this->elementFileImporter->checkMainFileUpload($releaseFile);
                }
                $prodIds = [];
                foreach ($command->releaseFiles as $releaseFile) {
                    $prodIds[] = $this->createProd($command, $releaseFile);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new ProdDataException($exception->getMessage(), 400, $exception);
            }
            return $prodIds;
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new CreatedIdsDto($prodIds);
    }

    /**
     * @throws InvalidReferenceException
     * @throws AttachedFileRefusedException
     */
    private function createProd(BatchUploadProdsCommand $command, UploadedFileDto $releaseFile): int
    {
        $title = trim($command->fields->title ?? '');
        if ($title === '') {
            $title = str_replace('_', ' ', ucfirst(pathinfo($releaseFile->fileName, PATHINFO_FILENAME)));
        }
        $prod = $this->prodCreator->create(
            new ProdFieldsDto(...array_replace(get_object_vars($command->fields), ['title' => $title])),
        );
        $prodId = $prod->getId();
        $this->elementAuthorsWriter->replace($prodId, EntityType::Prod, $command->authors);
        foreach ($command->files as $upload) {
            $this->elementFileImporter->addUploadedFile($prod, $upload->kind, $upload->file);
        }
        $release = $this->releaseCreator->create(new ReleaseFieldsDto(title: $title, prodId: $prodId));
        $this->elementFileImporter->setUploadedMainFile($release, $releaseFile);
        $this->actionsLogService->log($prod, StructureType::ZxProd, self::PRIVILEGE);

        return $prodId;
    }
}
