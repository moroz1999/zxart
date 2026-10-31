<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Groups;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\CreditChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Groups\Dto\GroupDataResultDto;
use ZxArt\Groups\Services\GroupStateService;
use ZxArt\Groups\UseCases\SetGroupMemberCommand;
use ZxArt\Groups\UseCases\SetGroupMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<SetGroupMemberRequestDto>
 *
 * @psalm-api
 */
final readonly class SetGroupMemberTool implements WriteTool
{
    public function __construct(
        private SetGroupMemberHandler $setGroupMemberHandler,
        private GroupStateService $groupStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_group_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Makes an author or an author alias a member of a group with the roles and dates given, or changes '
            . 'the roles and dates of a member.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SetGroupMemberRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->setGroupMemberHandler->check($this->command($request));
        $state = $this->groupStateService->get($request->groupId);

        return new ChangePreviewDto(
            kind: ChangeKind::Link,
            target: new ChangeTargetDto(EntityType::Group, $request->groupId, (string)$state->fields->title),
            changes: $this->creditChanges->set($state->members, $request->authorId, $request->roles, $request->startDate, $request->endDate),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->setGroupMemberHandler->handle($this->command($request));
    }

    private function command(SetGroupMemberRequestDto $request): SetGroupMemberCommand
    {
        return new SetGroupMemberCommand($request->groupId, $request->authorId, $request->roles, $request->startDate, $request->endDate);
    }
}
