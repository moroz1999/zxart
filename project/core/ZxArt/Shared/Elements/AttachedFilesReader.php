<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

use fileElement;
use linksManager;
use structureManager;
use ZxArt\Shared\Dto\AttachedFileDto;
use zxProdElement;
use zxReleaseElement;

/**
 * Reads one file list of a production or a release. The ids come from the
 * links: the element's own list holds only what it loaded or was given since.
 *
 * @psalm-api
 */
final readonly class AttachedFilesReader
{
    public function __construct(
        private linksManager $linksManager,
        private structureManager $structureManager,
    ) {
    }

    /**
     * @return AttachedFileDto[] in the order the files were added
     */
    public function read(zxProdElement|zxReleaseElement $element, AttachedFileKind $kind): array
    {
        /** @var array<int|string> $fileIds */
        $fileIds = $this->linksManager->getConnectedIdList(
            $element->getPersistedId(),
            $element->getConnectedFileType($kind->propertyName()),
            'parent',
        );
        $fileIds = array_map('intval', $fileIds);
        sort($fileIds);

        $files = [];
        foreach ($fileIds as $fileId) {
            $file = $this->structureManager->getElementById($fileId);
            if ($file instanceof fileElement) {
                $files[] = new AttachedFileDto($fileId, ElementFields::text($file, 'fileName'));
            }
        }

        return $files;
    }
}
