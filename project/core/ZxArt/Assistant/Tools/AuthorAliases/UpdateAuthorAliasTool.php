<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\AuthorAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Authors\Dto\AuthorAliasDataResultDto;
use ZxArt\Authors\Services\AuthorAliasStateService;
use ZxArt\Authors\UseCases\UpdateAuthorAliasCommand;
use ZxArt\Authors\UseCases\UpdateAuthorAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<UpdateAuthorAliasRequestDto>
 *
 * @psalm-api
 */
final readonly class UpdateAuthorAliasTool implements WriteTool
{
    public function __construct(
        private UpdateAuthorAliasHandler $updateAuthorAliasHandler,
        private AuthorAliasStateService $authorAliasStateService,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'update_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Changes the fields of an author alias that are given and leaves the others; a list given replaces '
            . 'the whole list, an empty string or list clears the field.';
    }

    #[Override]
    public function requestClass(): string
    {
        return UpdateAuthorAliasRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->authorAliasStateService->get($request->id);

        return new ChangePreviewDto(
            kind: ChangeKind::Update,
            target: new ChangeTargetDto(EntityType::AuthorAlias, $request->id, (string)$state->fields->title),
            changes: $this->fieldChanges->diff($state->fields, $request->fields),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): AuthorAliasDataResultDto
    {
        return $this->updateAuthorAliasHandler->handle($this->command($request));
    }

    private function command(UpdateAuthorAliasRequestDto $request): UpdateAuthorAliasCommand
    {
        return new UpdateAuthorAliasCommand($request->id, $request->fields);
    }
}
