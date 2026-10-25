<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Releases\Services;

use errorLogger;
use Illuminate\Database\Connection;
use ProdsDownloader;
use structureManager;
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Prods\Services\ReleaseDownloadException;
use zxReleaseElement;

/**
 * Finds the stored release an imported one already is: the release whose
 * registered files are exactly the files of the downloaded release. A release
 * that cannot be downloaded is logged and found nowhere.
 */
final class ReleaseByFilesFinder extends errorLogger
{
    public function __construct(
        private readonly ProdsDownloader $prodsDownloader,
        private readonly ZxParsingManager $zxParsingManager,
        private readonly ReleaseFileTypesGatherer $releaseFileTypesGatherer,
        private readonly Connection $db,
        private readonly structureManager $structureManager,
    ) {
    }

    public function find(ReleaseImportDTO $dto, string $prodImportId): ?zxReleaseElement
    {
        try {
            return $this->findDownloaded($dto, $prodImportId);
        } catch (ReleaseDownloadException $e) {
            $this->logError($e->getMessage());
            return null;
        }
    }

    /**
     * @throws ReleaseDownloadException
     */
    private function findDownloaded(ReleaseImportDTO $dto, string $prodImportId): ?zxReleaseElement
    {
        if (!$dto->fileUrl) {
            return null;
        }
        if (empty($dto->md5)) {
            $path = $this->prodsDownloader->getDownloadedPath($dto->fileUrl);
            if (!$path) {
                sleep(10);
                $path = $this->prodsDownloader->getDownloadedPath($dto->fileUrl);
            }
            if (!$path) {
                throw new ReleaseDownloadException('Unable to download release ' . $prodImportId . ' ' . $dto->id . ' ' . $dto->title . ' ' . $dto->fileUrl);
            }
            if ($structure = $this->zxParsingManager->parseFileStructure($path)) {
                $releaseFiles = $this->releaseFileTypesGatherer->gatherReleaseFiles($structure);
                $index = [];
                if ($records = $this->db->table('files_registry')
                    ->whereIn('md5', array_keys($releaseFiles))
                    ->get()) {
                    $foundReleaseId = false;
                    foreach ($records as $record) {
                        $index[$record['elementId']][$record['md5']] = true;
                    }
                    foreach ($index as $elementId => $md5Index) {
                        if (count($index[$elementId]) === count($releaseFiles)) {
                            $foundReleaseId = $elementId;
                            break;
                        }
                    }
                    $release = $foundReleaseId ? $this->structureManager->getElementById($foundReleaseId) : null;
                    return $release instanceof zxReleaseElement ? $release : null;
                }
            }
        }
        return null;
    }
}
