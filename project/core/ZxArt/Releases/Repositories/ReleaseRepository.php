<?php

declare(strict_types=1);

namespace ZxArt\Releases\Repositories;

use structureManager;
use ZxArt\Releases\Exception\ReleaseDataException;
use zxReleaseElement;

/**
 * Supplies releases to the use cases and stores them: every write of a
 * release's own data, from the editors and the imports alike, goes through
 * `save()`.
 *
 * @psalm-api
 */
final readonly class ReleaseRepository
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    /**
     * @throws ReleaseDataException 400 without an id, 404 for no release of it
     */
    public function get(int $releaseId): zxReleaseElement
    {
        if ($releaseId <= 0) {
            throw new ReleaseDataException('Missing required release id', 400);
        }
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ReleaseDataException('Release not found', 404);
        }

        return $release;
    }

    /**
     * A new release under the production, its structure record not yet stored.
     *
     * @throws ReleaseDataException 400 without a production, 500 when the
     *     element cannot be made
     */
    public function create(int $prodId): zxReleaseElement
    {
        if ($prodId <= 0) {
            throw new ReleaseDataException('A release needs its production', 400);
        }
        $release = $this->structureManager->createElement('zxRelease', 'show', $prodId);
        if (!$release instanceof zxReleaseElement) {
            throw new ReleaseDataException('The release could not be created', 500);
        }

        return $release;
    }

    public function save(zxReleaseElement $release): void
    {
        $release->persistElementData();
    }

    /**
     * Moves the modification time after a change kept outside the element:
     * authorship, files.
     */
    public function touch(zxReleaseElement $release): void
    {
        $release->persistModificationTime();
    }

    public function delete(zxReleaseElement $release): void
    {
        $release->deleteElementData();
    }
}
