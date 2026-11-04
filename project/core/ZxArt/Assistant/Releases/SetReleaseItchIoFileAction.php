<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\ItchIo\ItchIoDownloads;
use ZxArt\Releases\ItchIo\ItchIoUpload;
use ZxArt\Releases\UseCases\SetReleaseFileCommand;
use ZxArt\Releases\UseCases\SetReleaseFileHandler;
use ZxArt\Shared\EntityType;

/**
 * Downloads a file of an itch.io game as the release file. itch.io gives a
 * file's address only to the session that opened the game's downloads, and
 * for a minute, so the address is asked when the action runs.
 *
 * @psalm-api
 */
final readonly class SetReleaseItchIoFileAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private ItchIoDownloads $itchIoDownloads,
        private SetReleaseFileHandler $setReleaseFileHandler,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_release_itch_io_file';
    }

    #[Override]
    public function description(): string
    {
        return 'Downloads a file of an itch.io game (an upload id from list_itch_io_files) and makes it the '
            . 'release\'s file under the name itch.io stores it with, replacing the one it has; its structure and '
            . 'formats are read from it as for an upload.';
    }

    #[Override]
    public function schema(): string
    {
        return SetReleaseItchIoFileArgumentsDto::SCHEMA;
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
        return $this->releaseDtos->release(SetReleaseItchIoFileArgumentsDto::from($arguments)->releaseId);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = SetReleaseItchIoFileArgumentsDto::from($arguments);
        $page = $this->itchIoDownloads->open($argumentsDto->game);
        $uploadIds = array_map(static fn(ItchIoUpload $upload): int => $upload->id, $page->uploads);
        $isOffered = in_array($argumentsDto->uploadId, $uploadIds, true);
        if (!$isOffered) {
            throw new ToolArgumentsException(
                "{$argumentsDto->game->gameUrl} offers no file {$argumentsDto->uploadId}; its files: " . implode(', ', $uploadIds),
            );
        }
        $fileUrl = $this->itchIoDownloads->fileUrl($page, $argumentsDto->uploadId);

        return $this->setReleaseFileHandler->handle(new SetReleaseFileCommand($argumentsDto->releaseId, $fileUrl, null));
    }
}
