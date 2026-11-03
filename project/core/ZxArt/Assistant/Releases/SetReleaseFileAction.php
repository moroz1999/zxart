<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\UseCases\SetReleaseFileCommand;
use ZxArt\Releases\UseCases\SetReleaseFileHandler;
use ZxArt\Shared\EntityType;

/**
 * Downloads the release file of a release.
 *
 * @psalm-api
 */
final readonly class SetReleaseFileAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private SetReleaseFileHandler $setReleaseFileHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_release_file';
    }

    #[Override]
    public function description(): string
    {
        return 'Downloads a file from the web and makes it the release\'s file, replacing the one it has; its '
            . 'structure and formats are read from it as for an upload.';
    }

    #[Override]
    public function schema(): string
    {
        return SetReleaseFileArgumentsDto::SCHEMA;
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
        return $this->releaseDtos->release(SetReleaseFileArgumentsDto::from($arguments)->releaseId);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = SetReleaseFileArgumentsDto::from($arguments);

        return $this->setReleaseFileHandler->handle(new SetReleaseFileCommand($argumentsDto->releaseId, $argumentsDto->url, $argumentsDto->fileName));
    }
}
