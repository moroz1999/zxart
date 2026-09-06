<?php

declare(strict_types=1);

namespace ZxArt\Forms;

use App\Paths\PathsManager;
use FilesElementInterface;

/**
 * Deletes the uploads a submitted form staged in the upload cache.
 *
 * Receiving an upload only reads it, because one submit may create several
 * elements that all need the same file. Whoever received the form is therefore
 * what ends its life, and this is how.
 */
final readonly class StagedUploadsCleaner
{
    public function __construct(
        private PathsManager $pathsManager,
    ) {
    }

    public function clearElementUploads(FilesElementInterface $element): void
    {
        foreach ($element->getFileSelectorPropertyNames() as $propertyName) {
            $this->clear($element->getSubmittedUploads($propertyName));
        }
    }

    /**
     * @param list<array<array-key, mixed>> $uploads as PHP described them, which
     *        is what the data chunk named the staged file after
     */
    public function clear(array $uploads): void
    {
        $cachePath = (string)$this->pathsManager->getPath('uploadsCache');
        foreach ($uploads as $upload) {
            $stagedFile = $cachePath . basename((string)($upload['tmp_name'] ?? ''));
            if (is_file($stagedFile)) {
                unlink($stagedFile);
            }
        }
    }
}
