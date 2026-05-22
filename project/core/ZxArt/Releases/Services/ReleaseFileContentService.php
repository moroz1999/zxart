<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use structureManager;
use ZxArt\Prods\Exception\ProdDetailsException;
use ZxArt\Releases\Dto\ReleaseFileContentDto;
use zxReleaseElement;

readonly class ReleaseFileContentService
{
    public function __construct(
        private structureManager $structureManager,
    ) {
    }

    public function getContent(int $releaseId, int $fileId): ReleaseFileContentDto
    {
        $release = $this->structureManager->getElementById($releaseId);
        if (!$release instanceof zxReleaseElement) {
            throw new ProdDetailsException('Release not found', 404);
        }

        $fileRecord = $this->findReleaseFileRecord($release, $fileId);
        if ($fileRecord === null) {
            throw new ProdDetailsException('File not found', 404);
        }

        return new ReleaseFileContentDto(
            id: (int)$fileRecord['id'],
            fileName: (string)$fileRecord['fileName'],
            size: (int)$fileRecord['size'],
            md5: (string)$fileRecord['md5'],
            contentHtml: $release->getFormattedFileContent($fileRecord),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findReleaseFileRecord(zxReleaseElement $release, int $fileId): ?array
    {
        $records = $release->getReleaseFlatStructure();
        if ($records === false) {
            return null;
        }

        foreach ($records as $record) {
            if ($record['id'] === $fileId) {
                return $record;
            }
        }

        return null;
    }
}
