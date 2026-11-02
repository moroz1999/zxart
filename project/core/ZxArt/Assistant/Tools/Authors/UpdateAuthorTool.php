<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Authors;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorDataResultDto;
use ZxArt\Authors\Services\AuthorStateService;
use ZxArt\Authors\UseCases\UpdateAuthorCommand;
use ZxArt\Authors\UseCases\UpdateAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<UpdateAuthorRequestDto>
 *
 * @psalm-api
 */
final readonly class UpdateAuthorTool implements WriteTool
{
    public function __construct(
        private UpdateAuthorHandler $updateAuthorHandler,
        private AuthorStateService $authorStateService,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of an author that are given and leaves the others; a list given replaces the '
            . 'whole list, an empty string or list clears the field.';
    }

    #[Override]
    public function requestClass(): string
    {
        return UpdateAuthorRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->authorStateService->get($request->id);

        return new ChangePreviewDto(
            kind: ChangeKind::Update,
            target: new ChangeTargetDto(EntityType::Author, $request->id, (string)$state->fields->title),
            changes: $this->fieldChanges->diff($state->fields, $request->fields),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorDataResultDto
    {
        return $this->updateAuthorHandler->handle($this->command($request));
    }

    private function command(UpdateAuthorRequestDto $request): UpdateAuthorCommand
    {
        return new UpdateAuthorCommand($request->id, $request->fields);
    }
}
