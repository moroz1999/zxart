<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Groups\UseCases\UpdateGroupCommand;
use ZxArt\Groups\UseCases\UpdateGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<UpdateGroupRequestDto>
 *
 * @psalm-api
 */
final readonly class UpdateGroupTool implements WriteTool
{
    public function __construct(
        private UpdateGroupHandler $updateGroupHandler,
        private GroupStateService $groupStateService,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a group that are given and leaves the others; a list given replaces the '
            . 'whole list, an empty string or list clears the field.';
    }

    #[Override]
    public function requestClass(): string
    {
        return UpdateGroupRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->updateGroupHandler->check($this->command($request));
        $state = $this->groupStateService->get($request->id);

        return new ChangePreviewDto(
            kind: ChangeKind::Update,
            target: new ChangeTargetDto(EntityType::Group, $request->id, (string)$state->fields->title),
            changes: $this->fieldChanges->diff($state->fields, $request->fields),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->updateGroupHandler->handle($this->command($request));
    }

    private function command(UpdateGroupRequestDto $request): UpdateGroupCommand
    {
        return new UpdateGroupCommand($request->id, $request->fields);
    }
}
