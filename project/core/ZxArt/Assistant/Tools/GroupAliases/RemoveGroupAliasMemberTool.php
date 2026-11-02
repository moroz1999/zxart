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
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberCommand;
use ZxArt\Groups\UseCases\DeleteGroupAliasMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<RemoveGroupAliasMemberRequestDto>
 *
 * @psalm-api
 */
final readonly class RemoveGroupAliasMemberTool implements WriteTool
{
    public function __construct(
        private DeleteGroupAliasMemberHandler $deleteGroupAliasMemberHandler,
        private GroupAliasStateService $groupAliasStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_group_alias_member';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes an author or an author alias from the members of a group alias.';
    }

    #[Override]
    public function requestClass(): string
    {
        return RemoveGroupAliasMemberRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $state = $this->groupAliasStateService->get($request->aliasId);

        return new ChangePreviewDto(
            kind: ChangeKind::Unlink,
            target: new ChangeTargetDto(EntityType::GroupAlias, $request->aliasId, (string)$state->fields->title),
            changes: $this->creditChanges->remove($state->members, $request->authorId),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): GroupAliasDataResultDto
    {
        return $this->deleteGroupAliasMemberHandler->handle($this->command($request));
    }

    private function command(RemoveGroupAliasMemberRequestDto $request): DeleteGroupAliasMemberCommand
    {
        return new DeleteGroupAliasMemberCommand($request->aliasId, $request->authorId);
    }
}
