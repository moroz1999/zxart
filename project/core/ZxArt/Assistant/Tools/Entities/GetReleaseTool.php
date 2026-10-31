<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Entities;

use Override;
use ZxArt\Assistant\Tools\Dto\EntityIdRequestDto;
use ZxArt\Assistant\Tools\ReadTool;
use ZxArt\Releases\Dto\ReleaseCardDto;
use ZxArt\Releases\Services\ReleaseCardReader;

/**
 * @implements ReadTool<EntityIdRequestDto>
 *
 * @psalm-api
 */
final readonly class GetReleaseTool implements ReadTool
{
    public function __construct(
        private ReleaseCardReader $releaseCardReader,
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
        return 'Reads one release: every field as update_release takes it, the titles of the entities the fields '
            . 'name by id, the credited authors with roles, the files of each list with their ids, the release file '
            . 'name and size, and the hardware and languages of its production.';
    }

    #[Override]
    public function requestClass(): string
    {
        return EntityIdRequestDto::class;
    }

    #[Override]
    public function execute(object $request): ReleaseCardDto
    {
        return $this->releaseCardReader->read($request->id);
    }
}
