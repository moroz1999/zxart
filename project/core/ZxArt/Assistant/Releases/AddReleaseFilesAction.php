<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\UseCases\AddReleaseFilesCommand;
use ZxArt\Releases\UseCases\AddReleaseFilesHandler;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\EntityType;

/**
 * Downloads files into a file list of a release.
 *
 * @psalm-api
 */
final readonly class AddReleaseFilesAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private AddReleaseFilesHandler $addReleaseFilesHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'add_release_files';
    }

    #[Override]
    public function description(): string
    {
        return 'Downloads files from the web into a file list of a release: screenshots, inlays, info files or '
            . 'advertisements.';
    }

    #[Override]
    public function schema(): string
    {
        return AddReleaseFilesArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::AttachFile;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(AddReleaseFilesArgumentsDto::from($arguments)->releaseId);
    }

    #[Override]
    public function write(Arguments $arguments): AddedFilesDto
    {
        $argumentsDto = AddReleaseFilesArgumentsDto::from($arguments);

        return $this->addReleaseFilesHandler->handle(new AddReleaseFilesCommand($argumentsDto->releaseId, $argumentsDto->kind, $argumentsDto->urls));
    }
}
