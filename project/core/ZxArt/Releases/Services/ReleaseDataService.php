<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Releases\UseCases\AddReleaseFilesCommand;
use ZxArt\Releases\UseCases\AddReleaseFilesHandler;
use ZxArt\Releases\UseCases\CloneReleaseCommand;
use ZxArt\Releases\UseCases\CloneReleaseHandler;
use ZxArt\Releases\UseCases\CreateReleaseCommand;
use ZxArt\Releases\UseCases\CreateReleaseHandler;
use ZxArt\Releases\UseCases\DeleteReleaseCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileHandler;
use ZxArt\Releases\UseCases\DeleteReleaseHandler;
use ZxArt\Releases\UseCases\DeleteReleaseMemberCommand;
use ZxArt\Releases\UseCases\DeleteReleaseMemberHandler;
use ZxArt\Releases\UseCases\SetReleaseFileCommand;
use ZxArt\Releases\UseCases\SetReleaseFileHandler;
use ZxArt\Releases\UseCases\SetReleaseMemberCommand;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
use ZxArt\Releases\UseCases\UpdateReleaseCommand;
use ZxArt\Releases\UseCases\UpdateReleaseHandler;
use ZxArt\Shared\Dto\AddedFilesDto;

/**
 * Changes to a release requested through `/release-data/`, each one a use case
 * in `ZxArt\Releases\UseCases`: it checks the privilege of the matching legacy
 * action, runs in one transaction and is recorded in the actions log.
 *
 * @psalm-api
 */
final readonly class ReleaseDataService
{
    public function __construct(
        private CreateReleaseHandler $createReleaseHandler,
        private UpdateReleaseHandler $updateReleaseHandler,
        private DeleteReleaseHandler $deleteReleaseHandler,
        private CloneReleaseHandler $cloneReleaseHandler,
        private SetReleaseMemberHandler $setReleaseMemberHandler,
        private DeleteReleaseMemberHandler $deleteReleaseMemberHandler,
        private AddReleaseFilesHandler $addReleaseFilesHandler,
        private SetReleaseFileHandler $setReleaseFileHandler,
        private DeleteReleaseFileHandler $deleteReleaseFileHandler,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function create(ReleaseFieldsDto $fields): ReleaseDataResultDto
    {
        return $this->createReleaseHandler->handle(new CreateReleaseCommand($fields));
    }

    /**
     * Changes the fields the DTO carries and leaves the others as they are.
     *
     * @throws ReleaseDataException
     */
    public function update(int $releaseId, ReleaseFieldsDto $fields): ReleaseDataResultDto
    {
        return $this->updateReleaseHandler->handle(new UpdateReleaseCommand($releaseId, $fields));
    }

    /**
     * @throws ReleaseDataException
     */
    public function delete(int $releaseId): ReleaseDataResultDto
    {
        return $this->deleteReleaseHandler->handle(new DeleteReleaseCommand($releaseId));
    }

    /**
     * @param list<string> $roles none stands for an unknown role
     * @throws ReleaseDataException
     */
    public function setMember(int $releaseId, int $authorId, array $roles): ReleaseDataResultDto
    {
        return $this->setReleaseMemberHandler->handle(new SetReleaseMemberCommand($releaseId, $authorId, $roles));
    }

    /**
     * @throws ReleaseDataException
     */
    public function deleteMember(int $releaseId, int $authorId): ReleaseDataResultDto
    {
        return $this->deleteReleaseMemberHandler->handle(new DeleteReleaseMemberCommand($releaseId, $authorId));
    }

    /**
     * @throws ReleaseDataException
     */
    public function cloneRelease(int $releaseId): ReleaseDataResultDto
    {
        return $this->cloneReleaseHandler->handle(new CloneReleaseCommand($releaseId));
    }

    /**
     * @param string[] $urls
     * @throws ReleaseDataException
     */
    public function addFiles(int $releaseId, ReleaseFileKind $kind, array $urls): AddedFilesDto
    {
        return $this->addReleaseFilesHandler->handle(new AddReleaseFilesCommand($releaseId, $kind, $urls));
    }

    /**
     * @throws ReleaseDataException
     */
    public function setFile(int $releaseId, string $url, ?string $fileName): ReleaseDataResultDto
    {
        return $this->setReleaseFileHandler->handle(new SetReleaseFileCommand($releaseId, $url, $fileName));
    }

    /**
     * @throws ReleaseDataException
     */
    public function deleteFile(int $releaseId, int $fileId): ReleaseDataResultDto
    {
        return $this->deleteReleaseFileHandler->handle(new DeleteReleaseFileCommand($releaseId, $fileId));
    }
}
