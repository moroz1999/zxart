<?php

declare(strict_types=1);

namespace ZxArt\Releases\UseCases;

use App\Users\CurrentUserService;
use ZxArt\ActionsLog\ActionsLogService;
use ZxArt\Authors\Services\ElementAuthorsWriter;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ElementFileImporter;
use ZxArt\Releases\Services\ReleaseCreator;
use ZxArt\Releases\Services\ReleasePrivileges;
use ZxArt\Shared\Dto\CreatedIdsDto;
use ZxArt\Shared\Dto\UploadedFileDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\InvalidReferenceException;
use ZxArt\Shared\StructureType;
use ZxArt\Shared\UseCases\TransactionRunner;

/**
 * A production gains a release per uploaded file, each with the values typed
 * once — the authors and the screenshots, inlays and other files among them —
 * and only its own file differing. A release without a title is titled after
 * its file: the name without the extension, underscores as spaces, the first
 * letter capital. Without a file it makes the one release described.
 *
 * All in one transaction: a wrong reference, role or file creates nothing.
 *
 * @psalm-api
 */
final readonly class CreateReleasesFromFilesHandler
{
    /** Held on the production: adding a release to it. */
    private const string PRIVILEGE = 'publicAdd';

    public function __construct(
        private TransactionRunner $transactionRunner,
        private ReleasePrivileges $releasePrivileges,
        private ReleaseCreator $releaseCreator,
        private ElementAuthorsWriter $elementAuthorsWriter,
        private ElementFileImporter $elementFileImporter,
        private ActionsLogService $actionsLogService,
        private CurrentUserService $currentUserService,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function handle(CreateReleasesFromFilesCommand $command): CreatedIdsDto
    {
        $prodId = $command->fields->prodId;
        if ($prodId === null || $prodId <= 0) {
            throw new ReleaseDataException('A release needs its production', 400);
        }
        $this->releasePrivileges->assertMayAddTo($prodId);

        $releaseIds = $this->transactionRunner->run(function () use ($command): array {
            try {
                foreach ($command->files as $upload) {
                    $this->elementFileImporter->checkUpload($upload->kind, $upload->file);
                }
                foreach ($command->releaseFiles as $releaseFile) {
                    $this->elementFileImporter->checkMainFileUpload($releaseFile);
                }
                $releaseIds = [];
                foreach ($command->releaseFiles !== [] ? $command->releaseFiles : [null] as $releaseFile) {
                    $releaseIds[] = $this->createRelease($command, $releaseFile);
                }
            } catch (InvalidReferenceException|AttachedFileRefusedException $exception) {
                throw new ReleaseDataException($exception->getMessage(), 400, $exception);
            }
            return $releaseIds;
        });
        $this->currentUserService->getCurrentUser()->refreshPrivileges();

        return new CreatedIdsDto($releaseIds);
    }

    /**
     * @throws InvalidReferenceException
     * @throws AttachedFileRefusedException
     */
    private function createRelease(CreateReleasesFromFilesCommand $command, ?UploadedFileDto $releaseFile): int
    {
        $title = trim($command->fields->title ?? '');
        if ($title === '' && $releaseFile !== null) {
            $title = str_replace('_', ' ', ucfirst(pathinfo($releaseFile->fileName, PATHINFO_FILENAME)));
        }
        $release = $this->releaseCreator->create(
            new ReleaseFieldsDto(...array_replace(get_object_vars($command->fields), ['title' => $title])),
        );
        $releaseId = $release->getId();
        $this->elementAuthorsWriter->replace($releaseId, EntityType::Release, $command->authors);
        foreach ($command->files as $upload) {
            $this->elementFileImporter->addUploadedFile($release, $upload->kind, $upload->file);
        }
        if ($releaseFile !== null) {
            $this->elementFileImporter->setUploadedMainFile($release, $releaseFile);
        }
        $this->actionsLogService->log($release, StructureType::ZxRelease, self::PRIVILEGE);

        return $releaseId;
    }
}
