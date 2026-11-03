<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Releases;

use Override;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\IdArgumentsDto;
use ZxArt\Assistant\Actions\ReadAction;

/**
 * Reads one release.
 *
 * @psalm-api
 */
final readonly class GetReleaseAction implements ReadAction
{
    public function __construct(
        private ReleaseDtos $releaseDtos,
    ) {
    }

    #[Override]
    public function name(): string
    {
        return 'get_release';
    }

    #[Override]
    public function description(): string
    {
        return 'Reads one release: its fields as update_release takes them, the titles of the entities its ids name, its '
            . 'authors with roles, its files, the release file\'s name and size, and the hardware and languages of its '
            . 'production, which a release inherits.';
    }

    #[Override]
    public function schema(): string
    {
        return IdArgumentsDto::SCHEMA;
    }

    #[Override]
    public function read(Arguments $arguments): ReleaseDto
    {
        return $this->releaseDtos->release(IdArgumentsDto::from($arguments)->id);
    }
}
