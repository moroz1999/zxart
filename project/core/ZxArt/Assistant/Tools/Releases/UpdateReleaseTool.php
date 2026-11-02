<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\UpdateReleaseCommand;
use ZxArt\Releases\UseCases\UpdateReleaseHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<UpdateReleaseRequestDto>
 *
 * @psalm-api
 */
final readonly class UpdateReleaseTool implements WriteTool
{
    public function __construct(
        private UpdateReleaseHandler $updateReleaseHandler,
        private ReleaseStateService $releaseStateService,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_release';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a release that are given and leaves the others; a list given replaces the '
            . 'whole list, an empty string or list clears the field.';
    }

    #[Override]
    public function requestClass(): string
    {
        return UpdateReleaseRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->releaseStateService->get($request->id);

        return new ChangePreviewDto(
            kind: ChangeKind::Update,
            target: new ChangeTargetDto(EntityType::Release, $request->id, (string)$state->fields->title),
            changes: $this->fieldChanges->diff($state->fields, $request->fields),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->updateReleaseHandler->handle($this->command($request));
    }

    private function command(UpdateReleaseRequestDto $request): UpdateReleaseCommand
    {
        return new UpdateReleaseCommand($request->id, $request->fields);
    }
}
