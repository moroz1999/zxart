<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\UseCases\UpdateGroupAliasCommand;
use ZxArt\Groups\UseCases\UpdateGroupAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<UpdateGroupAliasRequestDto>
 *
 * @psalm-api
 */
final readonly class UpdateGroupAliasTool implements WriteTool
{
    public function __construct(
        private UpdateGroupAliasHandler $updateGroupAliasHandler,
        private GroupAliasStateService $groupAliasStateService,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of a group alias that are given and leaves the others; a list given replaces '
            . 'the whole list, an empty string or list clears the field.';
    }

    #[Override]
    public function requestClass(): string
    {
        return UpdateGroupAliasRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->groupAliasStateService->get($request->id);

        return new ChangePreviewDto(
            kind: ChangeKind::Update,
            target: new ChangeTargetDto(EntityType::GroupAlias, $request->id, (string)$state->fields->title),
            changes: $this->fieldChanges->diff($state->fields, $request->fields),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->updateGroupAliasHandler->handle($this->command($request));
    }

    private function command(UpdateGroupAliasRequestDto $request): UpdateGroupAliasCommand
    {
        return new UpdateGroupAliasCommand($request->id, $request->fields);
    }
}
