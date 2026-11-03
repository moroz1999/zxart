<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\UseCases\DeleteReleaseFileCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileHandler;
use ZxArt\Shared\EntityType;

/**
 * Deletes a file of a release.
 *
 * @psalm-api
 */
final readonly class DeleteReleaseFileAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private DeleteReleaseFileHandler $deleteReleaseFileHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_release_file';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a file from the file lists of a release.';
    }

    #[Override]
    public function schema(): string
    {
        return DeleteReleaseFileArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::DeleteFile;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(DeleteReleaseFileArgumentsDto::from($arguments)->releaseId);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = DeleteReleaseFileArgumentsDto::from($arguments);

        return $this->deleteReleaseFileHandler->handle(new DeleteReleaseFileCommand($argumentsDto->releaseId, $argumentsDto->fileId));
    }
}
