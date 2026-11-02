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
use ZxArt\Authors\UseCases\CreateAuthorAliasCommand;
use ZxArt\Authors\UseCases\CreateAuthorAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<CreateAuthorAliasRequestDto>
 *
 * @psalm-api
 */
final readonly class CreateAuthorAliasTool implements WriteTool
{
    public function __construct(
        private CreateAuthorAliasHandler $createAuthorAliasHandler,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_author_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates an alias of the author authorId names, with the fields given.';
    }

    #[Override]
    public function requestClass(): string
    {
        return CreateAuthorAliasRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        return new ChangePreviewDto(
            kind: ChangeKind::Create,
            target: new ChangeTargetDto(EntityType::AuthorAlias, null, (string)$request->fields->title),
            changes: $this->fieldChanges->diff(null, $request->fields),
            consequences: [],
            modifiedAt: null,
        );
    }

    #[Override]
    public function execute(object $request): AuthorAliasDataResultDto
    {
        return $this->createAuthorAliasHandler->handle($this->command($request));
    }

    private function command(CreateAuthorAliasRequestDto $request): CreateAuthorAliasCommand
    {
        return new CreateAuthorAliasCommand($request->fields);
    }
}
