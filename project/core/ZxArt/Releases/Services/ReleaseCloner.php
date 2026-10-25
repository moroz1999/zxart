<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use ZxArt\Authors\Dbal\AuthorshipRepository;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Shared\EntityType;
use zxReleaseElement;

/**
 * Makes a new release of the same production from an existing one: its fields
 * and authors, and the same stored file, read again for the clone. The clone is
 * created as any release is ({@see ReleaseCreator}), so the user who clones it
 * may edit it; screenshots and other files stay with the source.
 *
 * Checks no privilege and writes no log — the caller does.
 *
 * @psalm-api
 */
final readonly class ReleaseCloner
{
    public function __construct(
        private ReleaseCreator $releaseCreator,
        private ReleaseFieldsReader $releaseFieldsReader,
        private AuthorshipRepository $authorshipRepository,
        private ReleaseRepository $releaseRepository,
    ) {
    }

    /**
     * @throws ReleaseDataException
     */
    public function clone(zxReleaseElement $source): zxReleaseElement
    {
        $sourceId = $source->getPersistedId();
        $clone = $this->releaseCreator->create($this->releaseFieldsReader->read($source));
        $cloneId = $clone->getPersistedId();

        $file = ElementFields::text($source, 'file');
        if ($file !== '') {
            $clone->file = $file;
            $clone->fileName = ElementFields::text($source, 'fileName');
            $clone->parsed = 0;
            $this->releaseRepository->save($clone);
            $clone->updateFileStructure();
        }

        foreach ($this->authorshipRepository->findByElement($sourceId, EntityType::Release) as $authorship) {
            $this->authorshipRepository->setAuthorship(
                $cloneId,
                $authorship->authorId,
                EntityType::Release,
                $authorship->roles,
                $authorship->startDate,
                $authorship->endDate,
            );
        }

        return $clone;
    }
}
