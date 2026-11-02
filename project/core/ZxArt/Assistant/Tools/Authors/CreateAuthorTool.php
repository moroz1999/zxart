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
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<CreateAuthorRequestDto>
 *
 * @psalm-api
 */
final readonly class CreateAuthorTool implements WriteTool
{
    public function __construct(
        private CreateAuthorHandler $createAuthorHandler,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates an author with the fields given; it is placed under the letter of its title. Add aliases '
            . 'with create_author_alias and memberships with set_group_member afterwards.';
    }

    #[Override]
    public function requestClass(): string
    {
        return CreateAuthorRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        return new ChangePreviewDto(
            kind: ChangeKind::Create,
            target: new ChangeTargetDto(EntityType::Author, null, (string)$request->fields->title),
            changes: $this->fieldChanges->diff(null, $request->fields),
            consequences: [],
            modifiedAt: null,
        );
    }

    #[Override]
    public function execute(object $request): AuthorDataResultDto
    {
        return $this->createAuthorHandler->handle($this->command($request));
    }

    private function command(CreateAuthorRequestDto $request): CreateAuthorCommand
    {
        return new CreateAuthorCommand($request->fields);
    }
}
