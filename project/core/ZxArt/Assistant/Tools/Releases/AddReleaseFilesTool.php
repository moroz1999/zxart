<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\AddReleaseFilesCommand;
use ZxArt\Releases\UseCases\AddReleaseFilesHandler;
use ZxArt\Shared\Dto\AddedFilesDto;
use ZxArt\Shared\Dto\AttachedFileDto;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<AddReleaseFilesRequestDto>
 *
 * @psalm-api
 */
final readonly class AddReleaseFilesTool implements WriteTool
{
    public function __construct(
        private AddReleaseFilesHandler $addReleaseFilesHandler,
        private ReleaseStateService $releaseStateService,
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
            . 'ads.';
    }

    #[Override]
    public function requestClass(): string
    {
        return AddReleaseFilesRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->releaseStateService->get($request->releaseId);
        $names = array_map(static fn(AttachedFileDto $file): string => $file->fileName, $state->files[$request->kind->value]);

        return new ChangePreviewDto(
            kind: ChangeKind::AttachFile,
            target: new ChangeTargetDto(EntityType::Release, $request->releaseId, (string)$state->fields->title),
            changes: [new FieldChangeDto($request->kind->value, $names, [...$names, ...$request->urls])],
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AddedFilesDto
    {
        return $this->addReleaseFilesHandler->handle($this->command($request));
    }

    private function command(AddReleaseFilesRequestDto $request): AddReleaseFilesCommand
    {
        return new AddReleaseFilesCommand($request->releaseId, $request->kind, $request->urls);
    }
}
