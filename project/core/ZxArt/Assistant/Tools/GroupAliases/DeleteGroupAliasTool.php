<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\UseCases\DeleteGroupAliasCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteGroupAliasTool implements WriteTool
{
    public function __construct(
        private DeleteGroupAliasHandler $deleteGroupAliasHandler,
        private GroupAliasStateService $groupAliasStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_group_alias';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a group alias.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->deleteGroupAliasHandler->check($this->command($request));
        $state = $this->groupAliasStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::GroupAlias, $request->id))->text();
        $holds = Quantities::phrase([
            [$state->counters->members, 'member', 'members'],
            [$state->counters->prods, 'production made', 'productions made'],
            [$state->counters->published, 'publication', 'publications'],
        ]);
        $consequences = $holds !== null ? ["$text has $holds"] : [];

        return new ChangePreviewDto(
            kind: ChangeKind::Delete,
            target: new ChangeTargetDto(EntityType::GroupAlias, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->deleteGroupAliasHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): DeleteGroupAliasCommand
    {
        return new DeleteGroupAliasCommand($request->id);
    }
}
