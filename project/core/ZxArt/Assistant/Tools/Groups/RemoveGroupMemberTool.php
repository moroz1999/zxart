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
use ZxArt\Groups\UseCases\DeleteGroupMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<RemoveGroupMemberRequestDto>
 *
 * @psalm-api
 */
final readonly class RemoveGroupMemberTool implements WriteTool
{
    public function __construct(
        private DeleteGroupMemberHandler $deleteGroupMemberHandler,
        private GroupStateService $groupStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_group_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes an author or an author alias from the members of a group.';
    }

    #[Override]
    public function requestClass(): string
    {
        return RemoveGroupMemberRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->deleteGroupMemberHandler->check($this->command($request));
        $state = $this->groupStateService->get($request->groupId);

        return new ChangePreviewDto(
            kind: ChangeKind::Unlink,
            target: new ChangeTargetDto(EntityType::Group, $request->groupId, (string)$state->fields->title),
            changes: $this->creditChanges->remove($state->members, $request->authorId),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupDataResultDto
    {
        return $this->deleteGroupMemberHandler->handle($this->command($request));
    }

    private function command(RemoveGroupMemberRequestDto $request): DeleteGroupMemberCommand
    {
        return new DeleteGroupMemberCommand($request->groupId, $request->authorId);
    }
}
