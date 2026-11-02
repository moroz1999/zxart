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
use ZxArt\Releases\UseCases\SetReleaseFileCommand;
use ZxArt\Releases\UseCases\SetReleaseFileHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<SetReleaseFileRequestDto>
 *
 * @psalm-api
 */
final readonly class SetReleaseFileTool implements WriteTool
{
    public function __construct(
        private SetReleaseFileHandler $setReleaseFileHandler,
        private ReleaseStateService $releaseStateService,
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
    public function requestClass(): string
    {
        return SetReleaseFileRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->releaseStateService->get($request->releaseId);
        $before = $state->fileName !== '' ? $state->fileName : null;

        return new ChangePreviewDto(
            kind: ChangeKind::AttachFile,
            target: new ChangeTargetDto(EntityType::Release, $request->releaseId, (string)$state->fields->title),
            changes: [new FieldChangeDto('file', $before, $request->fileName ?? $request->url)],
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->setReleaseFileHandler->handle($this->command($request));
    }

    private function command(SetReleaseFileRequestDto $request): SetReleaseFileCommand
    {
        return new SetReleaseFileCommand($request->releaseId, $request->url, $request->fileName);
    }
}
