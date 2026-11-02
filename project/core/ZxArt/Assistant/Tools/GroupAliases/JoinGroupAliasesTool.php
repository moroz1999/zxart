<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\UseCases\JoinGroupAliasesCommand;
use ZxArt\Groups\UseCases\JoinGroupAliasesHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;

/**
 * @implements WriteTool<JoinGroupAliasesRequestDto>
 *
 * @psalm-api
 */
final readonly class JoinGroupAliasesTool implements WriteTool
{
    public function __construct(
        private JoinGroupAliasesHandler $joinGroupAliasesHandler,
        private GroupAliasStateService $groupAliasStateService,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'join_group_aliases';
    }

    #[Override]
    public function description(): string
    {
        return 'Merges a duplicate alias into the main one: its productions, publications and members move, and '
            . 'the joined alias is deleted.';
    }

    #[Override]
    public function requestClass(): string
    {
        return JoinGroupAliasesRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $main = $this->groupAliasStateService->get($request->mainId);
        $joined = $this->groupAliasStateService->get($request->joinedId);
        $mainText = (new EntityMentionDto(EntityType::GroupAlias, $request->mainId))->text();
        $joinedText = (new EntityMentionDto(EntityType::GroupAlias, $request->joinedId))->text();
        $consequences = ["$joinedText is deleted", "Its productions, publications and members move to $mainText"];

        return new ChangePreviewDto(
            kind: ChangeKind::Join,
            target: new ChangeTargetDto(EntityType::GroupAlias, $request->mainId, (string)$main->fields->title),
            changes: [],
            consequences: $consequences,
            modifiedAt: $main->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->joinGroupAliasesHandler->handle($this->command($request));
    }

    private function command(JoinGroupAliasesRequestDto $request): JoinGroupAliasesCommand
    {
        return new JoinGroupAliasesCommand($request->mainId, $request->joinedId);
    }
}
