<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Groups\UseCases\DeleteGroupCommand;
use ZxArt\Groups\UseCases\DeleteGroupHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class DeleteGroupTool implements WriteTool
{
    public function __construct(
        private DeleteGroupHandler $deleteGroupHandler,
        private GroupStateService $groupStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'delete_group';
    }

    #[Override]
    public function description(): string
    {
        return 'Deletes a group.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->groupStateService->get($request->id);
        $text = (new EntityMentionDto(EntityType::Group, $request->id))->text();
        $holds = Quantities::phrase([
            [$state->counters->members, 'member', 'members'],
            [$state->counters->prods, 'production made', 'productions made'],
            [$state->counters->published, 'publication', 'publications'],
        ]);
        $consequences = $holds !== null ? ["$text has $holds"] : [];

        return new ChangePreviewDto(
            kind: ChangeKind::Delete,
            target: new ChangeTargetDto(EntityType::Group, $request->id, (string)$state->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->deleteGroupHandler->handle($this->command($request));
    }

    private function command(EntityIdRequestDto $request): DeleteGroupCommand
    {
        return new DeleteGroupCommand($request->id);
    }
}
