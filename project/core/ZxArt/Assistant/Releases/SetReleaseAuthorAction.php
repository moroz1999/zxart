<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\WriteAction;
use ZxArt\Assistant\Actions\ChangeKind;
use ZxArt\Releases\Dto\ReleaseDataResultDto;
use ZxArt\Releases\UseCases\SetReleaseMemberCommand;
use ZxArt\Releases\UseCases\SetReleaseMemberHandler;
use ZxArt\Shared\EntityType;

/**
 * Credits an author on a release, or replaces the roles.
 *
 * @psalm-api
 */
final readonly class SetReleaseAuthorAction implements WriteAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
        private SetReleaseMemberHandler $setReleaseMemberHandler,
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
        return 'Credits an author or an author alias on a release with the roles given, or replaces the roles of one '
            . 'credited already.';
    }

    #[Override]
    public function schema(): string
    {
        return SetReleaseAuthorArgumentsDto::SCHEMA;
    }

    #[Override]
    public function kind(): ChangeKind
    {
        return ChangeKind::Link;
    }

    #[Override]
    public function entityType(): EntityType
    {
        return EntityType::Release;
    }

    #[Override]
    public function state(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(SetReleaseAuthorArgumentsDto::from($arguments)->releaseId);
    }

    #[Override]
    public function write(Arguments $arguments): ReleaseDataResultDto
    {
        $argumentsDto = SetReleaseAuthorArgumentsDto::from($arguments);

        return $this->setReleaseMemberHandler->handle(new SetReleaseMemberCommand($argumentsDto->releaseId, $argumentsDto->authorId, $argumentsDto->roles));
    }
}
