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
use ZxArt\Groups\UseCases\CreateGroupAliasCommand;
use ZxArt\Groups\UseCases\CreateGroupAliasHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<CreateGroupAliasRequestDto>
 *
 * @psalm-api
 */
final readonly class CreateGroupAliasTool implements WriteTool
{
    public function __construct(
        private CreateGroupAliasHandler $createGroupAliasHandler,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates an alias of the group groupId names, with the fields given.';
    }

    #[Override]
    public function requestClass(): string
    {
        return CreateGroupAliasRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        return new ChangePreviewDto(
            kind: ChangeKind::Create,
            target: new ChangeTargetDto(EntityType::GroupAlias, null, (string)$request->fields->title),
            changes: $this->fieldChanges->diff(null, $request->fields),
            consequences: [],
            modifiedAt: null,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->createGroupAliasHandler->handle($this->command($request));
    }

    private function command(CreateGroupAliasRequestDto $request): CreateGroupAliasCommand
    {
        return new CreateGroupAliasCommand($request->fields);
    }
}
