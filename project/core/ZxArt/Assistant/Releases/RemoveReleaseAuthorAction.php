<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\UseCases\DeleteReleaseMemberCommand;
use ZxArt\Releases\UseCases\DeleteReleaseMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Removes a credit from a release.
 *
 * @psalm-api
 */
final readonly class RemoveReleaseAuthorAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private DeleteReleaseMemberHandler $deleteReleaseMemberHandler,
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
    public function schema(): string
    {
        return RemoveReleaseAuthorArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Unlink;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(RemoveReleaseAuthorArgumentsDto::from($arguments)->releaseId);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = RemoveReleaseAuthorArgumentsDto::from($arguments);

        return $this->deleteReleaseMemberHandler->handle(new DeleteReleaseMemberCommand($argumentsDto->releaseId, $argumentsDto->authorId));
    }
}
