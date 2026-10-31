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
use ZxArt\Releases\UseCases\SetReleaseMemberCommand;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * @implements WriteTool<SetReleaseAuthorRequestDto>
 *
 * @psalm-api
 */
final readonly class SetReleaseAuthorTool implements WriteTool
{
    public function __construct(
        private SetReleaseMemberHandler $setReleaseMemberHandler,
        private ReleaseStateService $releaseStateService,
        private CreditChanges $creditChanges,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'set_release_author';
    }

    #[Override]
    public function description(): string
    {
        return 'Credits an author or an author alias on a release with the roles given, or replaces the roles of '
            . 'one already credited.';
    }

    #[Override]
    public function requestClass(): string
    {
        return SetReleaseAuthorRequestDto::class;
    }

    #[Override]
    public function preview(object $request): ChangePreviewDto
    {
        $this->setReleaseMemberHandler->check($this->command($request));
        $state = $this->releaseStateService->get($request->releaseId);

        return new ChangePreviewDto(
            kind: ChangeKind::Link,
            target: new ChangeTargetDto(EntityType::Release, $request->releaseId, (string)$state->fields->title),
            changes: $this->creditChanges->set($state->authors, $request->authorId, $request->roles),
            consequences: [],
            modifiedAt: $state->modifiedAt,
        );
    }

    #[Override]
    public function execute(object $request): ReleaseDataResultDto
    {
        return $this->setReleaseMemberHandler->handle($this->command($request));
    }

    private function command(SetReleaseAuthorRequestDto $request): SetReleaseMemberCommand
    {
        return new SetReleaseMemberCommand($request->releaseId, $request->authorId, $request->roles);
    }
}
