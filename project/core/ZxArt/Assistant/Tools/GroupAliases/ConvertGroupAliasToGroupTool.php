<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupCommand;
use ZxArt\Groups\UseCases\ConvertGroupAliasToGroupHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class ConvertGroupAliasToGroupTool implements WriteTool
{
    public function __construct(
        private ConvertGroupAliasToGroupHandler $convertGroupAliasToGroupHandler,
        private GroupAliasStateService $groupAliasStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'convert_group_alias_to_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Turns an alias into a group of its own, of the same title, which takes its productions, '
            . 'publications, members and import origins; the alias is deleted.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->groupAliasStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::GroupAlias, $request->id))->text();

        return new ChangePreviewDto(
            kind: ChangeKind::Convert,
            target: new ChangeTargetDto(EntityType::GroupAlias, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: ["$text is deleted; a new group of the same title takes its productions, publications, members and import origins"],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->convertGroupAliasToGroupHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): ConvertGroupAliasToGroupCommand
    {
        return new ConvertGroupAliasToGroupCommand($request->id);
    }
}
