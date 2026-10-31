<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\Quantities;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Groups\UseCases\JoinGroupsCommand;
use ZxArt\Groups\UseCases\JoinGroupsHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<JoinGroupsRequestDto>
 *
 * @psalm-api
 */
final readonly class JoinGroupsTool implements WriteTool
{
    public function __construct(
        private JoinGroupsHandler $joinGroupsHandler,
        private GroupStateService $groupStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_groups';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate group into the main one: its members, productions and aliases move, and the '
            . 'joined group is deleted; with asAlias they go to a new alias of the main group named as the joined '
            . 'one.';
    }

    #[Override]
    public function requestClass(): string
    {
        return JoinGroupsRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->joinGroupsHandler->check($this->command($request));
        $main = $this->groupStateService->get($request->mainId);
        $joined = $this->groupStateService->get($request->joinedId);
        $mainText = (new EntityMentionDto(EntityType::Group, $request->mainId))->text();
        $joinedText = (new EntityMentionDto(EntityType::Group, $request->joinedId))->text();
        $moved = Quantities::phrase([
            [$joined->counters->members, 'member', 'members'],
            [$joined->counters->prods, 'production made', 'productions made'],
            [$joined->counters->published, 'publication', 'publications'],
        ]);
        $total = $joined->counters->members + $joined->counters->prods + $joined->counters->published;
        $consequences = ["$joinedText is deleted"];
        if ($request->asAlias) {
            $consequences[] = "A new alias of $mainText takes its title, its members and its productions";
        } elseif ($moved !== null) {
            $consequences[] = $moved . ($total === 1 ? ' moves' : ' move') . " to $mainText";
        }
        $consequences[] = "Its aliases and import origins move to $mainText";

        return new ChangePreviewDto(
            kind: ChangeKind::Join,
            target: new ChangeTargetDto(EntityType::Group, $request->mainId, (string)$main->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $main->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->joinGroupsHandler->handle($this->command($request));
    }

    private function command(JoinGroupsRequestDto $request): JoinGroupsCommand
    {
        return new JoinGroupsCommand($request->mainId, $request->joinedId, $request->asAlias);
    }
}
