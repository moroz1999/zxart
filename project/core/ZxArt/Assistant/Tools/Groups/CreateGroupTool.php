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
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<CreateGroupRequestDto>
 *
 * @psalm-api
 */
final readonly class CreateGroupTool implements WriteTool
{
    public function __construct(
        private CreateGroupHandler $createGroupHandler,
        private FieldChanges $fieldChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'create_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Creates a group with the fields given. Add aliases with create_group_alias and members with '
            . 'set_group_member afterwards.';
    }

    #[Override]
    public function requestClass(): string
    {
        return CreateGroupRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        return new ChangePreviewDto(
            kind: ChangeKind::Create,
            target: new ChangeTargetDto(EntityType::Group, null, (string)$request->fields->title),
            changes: $this->fieldChanges->diff(null, $request->fields),
            consequences: [],
            modifiedAt: null,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->createGroupHandler->handle($this->command($request));
    }

    private function command(CreateGroupRequestDto $request): CreateGroupCommand
    {
        return new CreateGroupCommand($request->fields);
    }
}
