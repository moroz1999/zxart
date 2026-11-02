<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\DeleteReleaseCommand;
use ZxArt\Releases\UseCases\DeleteReleaseHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteReleaseTool implements WriteTool
{
    public function __construct(
        private DeleteReleaseHandler $deleteReleaseHandler,
        private ReleaseStateService $releaseStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_release';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a release together with its file and files.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->releaseStateService->get($request->id);
        $consequences = [];

        return new ChangePreviewDto(
            kind: ChangeKind::Delete,
            target: new ChangeTargetDto(EntityType::Release, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->deleteReleaseHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): DeleteReleaseCommand
    {
        return new DeleteReleaseCommand($request->id);
    }
}
