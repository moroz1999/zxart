<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\DeleteReleaseFileCommand;
use ZxArt\Releases\UseCases\DeleteReleaseFileHandler;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<DeleteReleaseFileRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteReleaseFileTool implements WriteTool
{
    public function __construct(
        private DeleteReleaseFileHandler $deleteReleaseFileHandler,
        private ReleaseStateService $releaseStateService,
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
    public function requestClass(): string
    {
        return DeleteReleaseFileRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->deleteReleaseFileHandler->check($this->command($request));
        $state = $this->releaseStateService->get($request->releaseId);
        $changes = [];
        foreach ($state->files as $kind => $files) {
            $names = array_map(static fn(AttachedFileDto $file): string => $file->fileName, $files);
            $left = array_map(
                static fn(AttachedFileDto $file): string => $file->fileName,
                array_values(array_filter($files, static fn(AttachedFileDto $file): bool => $file->id !== $request->fileId)),
            );
            if ($left !== $names) {
                $changes[] = new FieldChangeDto($kind, $names, $left);
            }
        }

        return new ChangePreviewDto(
            kind: ChangeKind::DeleteFile,
            target: new ChangeTargetDto(EntityType::Release, $request->releaseId, (string)$state->fields->title),
            changes: $changes,
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->deleteReleaseFileHandler->handle($this->command($request));
    }

    private function command(DeleteReleaseFileRequestDto $request): DeleteReleaseFileCommand
    {
        return new DeleteReleaseFileCommand($request->releaseId, $request->fileId);
    }
}
