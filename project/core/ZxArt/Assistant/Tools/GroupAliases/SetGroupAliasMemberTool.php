<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\GroupAliases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\CreditChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupAliasDataResultDto;
use ZxArt\Groups\Services\GroupAliasStateService;
use ZxArt\Groups\UseCases\SetGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\SetGroupAliasMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<SetGroupAliasMemberRequestDto>
 *
 * @psalm-api
 */
final readonly class SetGroupAliasMemberTool implements WriteTool
{
    public function __construct(
        private SetGroupAliasMemberHandler $setGroupAliasMemberHandler,
        private GroupAliasStateService $groupAliasStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_group_alias_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Makes an author or an author alias a member of a group alias with the roles and dates given, or '
            . 'changes the roles and dates of a member.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SetGroupAliasMemberRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->setGroupAliasMemberHandler->check($this->command($request));
        $state = $this->groupAliasStateService->get($request->aliasId);

        return new ChangePreviewDto(
            kind: ChangeKind::Link,
            target: new ChangeTargetDto(EntityType::GroupAlias, $request->aliasId, (string)$state->fields->title),
            changes: $this->creditChanges->set($state->members, $request->authorId, $request->roles, $request->startDate, $request->endDate),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->setGroupAliasMemberHandler->handle($this->command($request));
    }

    private function command(SetGroupAliasMemberRequestDto $request): SetGroupAliasMemberCommand
    {
        return new SetGroupAliasMemberCommand($request->aliasId, $request->authorId, $request->roles, $request->startDate, $request->endDate);
    }
}
