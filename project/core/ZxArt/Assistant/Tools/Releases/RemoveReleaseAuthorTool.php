<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Releases;

use Override;
use ZxArt\Assistant\Tools\Dto\ChangeKind;
use ZxArt\Assistant\Tools\Dto\ChangePreviewDto;
use ZxArt\Assistant\Tools\Dto\ChangeTargetDto;
use ZxArt\Assistant\Tools\Previews\CreditChanges;
use ZxArt\Assistant\Tools\WriteTool;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\Services\ReleaseStateService;
use ZxArt\Releases\UseCases\DeleteReleaseMemberCommand;
use ZxArt\Releases\UseCases\DeleteReleaseMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<RemoveReleaseAuthorRequestDto>
 *
 * @psalm-api
 */
final readonly class RemoveReleaseAuthorTool implements WriteTool
{
    public function __construct(
        private DeleteReleaseMemberHandler $deleteReleaseMemberHandler,
        private ReleaseStateService $releaseStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'remove_release_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Removes the credit of an author or an author alias from a release.';
    }

    #[Override]
    public function requestClass(): string
    {
        return RemoveReleaseAuthorRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->deleteReleaseMemberHandler->check($this->command($request));
        $state = $this->releaseStateService->get($request->releaseId);

        return new ChangePreviewDto(
            kind: ChangeKind::Unlink,
            target: new ChangeTargetDto(EntityType::Release, $request->releaseId, (string)$state->fields->title),
            changes: $this->creditChanges->remove($state->authors, $request->authorId),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->deleteReleaseMemberHandler->handle($this->command($request));
    }

    private function command(RemoveReleaseAuthorRequestDto $request): DeleteReleaseMemberCommand
    {
        return new DeleteReleaseMemberCommand($request->releaseId, $request->authorId);
    }
}
