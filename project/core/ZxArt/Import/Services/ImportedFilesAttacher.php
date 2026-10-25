<?php

// no strict types: sources hand numeric import ids over as ints, and the
// import has always let PHP coerce them to the strings the lookups take

namespace ZxArt\Import\Services;

use errorLogger;
use ZxArt\Import\Prods\Dto\FileWithAuthorDTO;
use ZxArt\Prods\Services\ReleaseDownloadException;
use ZxArt\Releases\Services\ElementFileImporter;
use zxProdElement;
use zxReleaseElement;

/**
 * Downloads the files an import names and attaches them to a production or a
 * release. A file that cannot be downloaded is logged and skipped.
 *
 * @psalm-api
 */
final class ImportedFilesAttacher extends errorLogger
{
    public function __construct(
        private readonly ElementFileImporter $elementFileImporter,
    ) {
    }

    /**
     * Attaches nothing to a property that already holds files, unless more
     * files may be added.
     *
     * @param string[] $fileUrls
     */
    public function attachUrls(zxReleaseElement|zxProdElement $element, array $fileUrls, string $propertyName, bool $addToExisting): void
    {
        if ($fileUrls === []) {
            return;
        }
        $existingFiles = $element->getFilesList($propertyName);
        if (!$existingFiles || $addToExisting) {
            foreach ($fileUrls as $fileUrl) {
                try {
                    $this->elementFileImporter->importFile($element, $fileUrl, $existingFiles, '', $propertyName);
                } catch (ReleaseDownloadException $e) {
                    $this->logError($e->getMessage());
                }
            }
        }
    }

    /**
     * @param FileWithAuthorDTO[] $files
     */
    public function attachWithAuthors(zxReleaseElement|zxProdElement $element, array $files, string $propertyName): void
    {
        $existingFiles = $element->getFilesList($propertyName);
        foreach ($files as $file) {
            try {
                $this->elementFileImporter->importFile($element, $file->url, $existingFiles, $file->author, $propertyName);
            } catch (ReleaseDownloadException $e) {
                $this->logError($e->getMessage());
            }
        }
    }
}
