<?php

declare(strict_types=1);

namespace ZxArt\Releases\Services;

use App\Paths\PathsManager;
use fileElement;
use ProdsDownloader;
use structureManager;
use ZxArt\Prods\Services\ReleaseDownloadException;
use ZxArt\Releases\Exception\AttachedFileRefusedException;
use ZxArt\Releases\Repositories\ReleaseRepository;
use ZxArt\Shared\Dto\UploadedFileDto;
use ZxArt\Shared\Elements\AttachedFileKind;
use ZxArt\Shared\Http\DownloadedFileDto;
use ZxArt\Shared\Http\DownloadRefusedException;
use ZxArt\Shared\Http\GuardedDownloader;
use zxProdElement;
use zxReleaseElement;

/**
 * Stores files fetched from a URL or uploaded as file elements of a production
 * or a release, and as a release's own file. The imports fetch through
 * `prodsDownloader`; URLs given by a user go through the guarded downloader.
 *
 * @psalm-api
 */
final readonly class ElementFileImporter
{
    /** For a URL without an extension. */
    private const array EXTENSIONS_BY_MIME_TYPE = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'png',
        'application/pdf' => 'pdf',
    ];
    private const int MAX_RELEASE_FILE_BYTES = 50 * 1024 * 1024;

    public function __construct(
        private structureManager $structureManager,
        private ProdsDownloader $prodsDownloader,
        private PathsManager $pathsManager,
        private GuardedDownloader $guardedDownloader,
        private ReleaseRepository $releaseRepository,
    ) {
    }

    /**
     * Downloads the file into the element's file list under `$propertyName`,
     * unless a file of that name is already stored there. A failed download is
     * tried twice more, after 10 and 20 seconds.
     *
     * @param fileElement[] $existingFiles
     * @throws ReleaseDownloadException
     */
    public function importFile(zxReleaseElement|zxProdElement $element, string $fileUrl, array $existingFiles, string|null $fileAuthor = null, string $propertyName = 'connectedFile'): void
    {
        $originalFileName = urldecode(basename($fileUrl));
        foreach ($existingFiles as $existingFile) {
            if ($originalFileName === urldecode($existingFile->fileName)) {
                $path = $existingFile->getFilePath();
                if (is_file($path) && filesize($path) > 0) {
                    return;
                }
            }
        }

        $filePath = (string)$this->pathsManager->getPath('uploads') . $originalFileName;
        $downloaded = $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
        if (!$downloaded) {
            sleep(10);
            $downloaded = $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
        }
        if (!$downloaded) {
            sleep(20);
            $downloaded = $this->prodsDownloader->downloadUrl($fileUrl, $filePath);
        }
        if (!$downloaded) {
            throw new ReleaseDownloadException('Unable to download release ' . $element->getId() . ' ' . $fileUrl);
        }
        $this->storeFile($element, $fileUrl, $filePath, $originalFileName, $fileAuthor, $propertyName);
    }

    /**
     * Downloads every URL, then stores them all in the list of that kind; a
     * refused download or a file the list does not take stores nothing.
     *
     * @param string[] $urls
     * @return int[] the new file elements, in the order of the URLs
     * @throws AttachedFileRefusedException
     */
    public function addFiles(zxReleaseElement|zxProdElement $element, AttachedFileKind $kind, array $urls): array
    {
        /** @var list<array{string, DownloadedFileDto}> $downloads */
        $downloads = [];
        try {
            foreach ($urls as $url) {
                $filePath = $this->temporaryPath();
                $downloads[] = [$filePath, $this->download($url, $filePath, $kind->maxBytes())];
                $contentType = $downloads[array_key_last($downloads)][1]->contentType;
                if (!$kind->accepts($contentType)) {
                    throw new AttachedFileRefusedException("A file of type $contentType cannot be added to {$kind->propertyName()}: $url");
                }
            }
        } catch (AttachedFileRefusedException $exception) {
            foreach ($downloads as [$filePath]) {
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
            throw $exception;
        }

        $fileIds = [];
        foreach ($downloads as [$filePath, $downloaded]) {
            $fileName = $this->fileNameOf($downloaded->finalUrl);
            $fileElement = $this->storeFile($element, $fileName, $filePath, $fileName, null, $kind->propertyName());
            if ($fileElement === null) {
                throw new AttachedFileRefusedException('The file element could not be created');
            }
            $fileIds[] = $fileElement->getPersistedId();
        }

        return $fileIds;
    }

    /**
     * Makes the download the release's file, under the given name or the one
     * in the URL, and reads its structure and formats as an upload does. A
     * refused download leaves the release's file as it was.
     *
     * @throws AttachedFileRefusedException
     */
    public function setMainFile(zxReleaseElement $release, string $url, ?string $fileName): void
    {
        $filePath = $this->temporaryPath();
        $downloaded = $this->download($url, $filePath, self::MAX_RELEASE_FILE_BYTES);

        $release->file = (string)$release->getPersistedId();
        $release->fileName = $fileName !== null && $fileName !== '' ? basename($fileName) : $this->fileNameOf($downloaded->finalUrl);
        $release->parsed = 0;
        rename($filePath, $release->getFilePath());
        $this->releaseRepository->save($release);
        $release->updateFileStructure();
    }

    /**
     * Deletes the release's own file and forgets it, with the formats read
     * from it.
     */
    public function removeMainFile(zxReleaseElement $release): void
    {
        if ($release->file !== '' && is_file($release->getFilePath())) {
            unlink($release->getFilePath());
        }
        $release->file = '';
        $release->fileName = '';
        $this->releaseRepository->save($release);
        $release->updateFileStructure();
    }

    /**
     * Refuses an uploaded file the list of that kind does not take: one too big
     * or of another type.
     *
     * @throws AttachedFileRefusedException
     */
    public function checkUpload(AttachedFileKind $kind, UploadedFileDto $file): void
    {
        $this->checkSize($file, $kind->maxBytes());
        $contentType = (string)mime_content_type($file->path);
        if (!$kind->accepts($contentType)) {
            throw new AttachedFileRefusedException("A file of type $contentType cannot be added to {$kind->propertyName()}: {$file->fileName}");
        }
    }

    /**
     * Stores a copy of the upload in the element's list of that kind; the
     * upload itself stays, so several elements can take the same one.
     *
     * @return int the new file element
     * @throws AttachedFileRefusedException
     */
    public function addUploadedFile(zxReleaseElement|zxProdElement $element, AttachedFileKind $kind, UploadedFileDto $file): int
    {
        $this->checkUpload($kind, $file);
        $filePath = $this->temporaryPath();
        copy($file->path, $filePath);
        $fileElement = $this->storeFile($element, $file->fileName, $filePath, $file->fileName, null, $kind->propertyName());
        if ($fileElement === null) {
            throw new AttachedFileRefusedException('The file element could not be created');
        }

        return $fileElement->getPersistedId();
    }

    /**
     * @throws AttachedFileRefusedException
     */
    public function checkMainFileUpload(UploadedFileDto $file): void
    {
        $this->checkSize($file, self::MAX_RELEASE_FILE_BYTES);
    }

    /**
     * Makes a copy of the upload the release's file, under the name it was sent
     * with, and reads its structure and formats.
     *
     * @throws AttachedFileRefusedException
     */
    public function setUploadedMainFile(zxReleaseElement $release, UploadedFileDto $file): void
    {
        $this->checkMainFileUpload($file);
        $release->file = (string)$release->getPersistedId();
        $release->fileName = basename($file->fileName);
        $release->parsed = 0;
        $directory = dirname($release->getFilePath());
        $this->pathsManager->ensureDirectory($directory . '/');
        if (!copy($file->path, $release->getFilePath())) {
            throw new AttachedFileRefusedException("The file {$file->fileName} could not be stored");
        }
        $this->releaseRepository->save($release);
        $release->updateFileStructure();
    }

    /**
     * @throws AttachedFileRefusedException
     */
    private function checkSize(UploadedFileDto $file, int $maxBytes): void
    {
        $size = @filesize($file->path);
        if ($size === false) {
            throw new AttachedFileRefusedException("The upload {$file->fileName} is missing");
        }
        if ($size > $maxBytes) {
            throw new AttachedFileRefusedException("{$file->fileName} is bigger than $maxBytes bytes");
        }
    }

    /**
     * @throws AttachedFileRefusedException
     */
    private function download(string $url, string $filePath, int $maxBytes): DownloadedFileDto
    {
        try {
            return $this->guardedDownloader->download($url, $filePath, $maxBytes);
        } catch (DownloadRefusedException $exception) {
            throw new AttachedFileRefusedException($exception->getMessage(), 0, $exception);
        }
    }

    private function temporaryPath(): string
    {
        $uploadsPath = (string)$this->pathsManager->getPath('uploads');
        $filePath = tempnam($uploadsPath, 'download');
        if ($filePath === false) {
            throw new AttachedFileRefusedException("Cannot create a file in $uploadsPath");
        }

        return $filePath;
    }

    private function fileNameOf(string $url): string
    {
        $fileName = urldecode(basename((string)parse_url($url, PHP_URL_PATH)));

        return $fileName !== '' ? $fileName : 'file';
    }

    /**
     * Creates the file element in the list under `$propertyName` and moves the
     * downloaded file to it. The title comes from the name in `$fileUrl`; a
     * name without an extension gets one by the file's type.
     */
    private function storeFile(zxReleaseElement|zxProdElement $element, string $fileUrl, string $filePath, string $originalFileName, ?string $fileAuthor, string $propertyName): ?fileElement
    {
        $fileElement = $this->structureManager->createElement(
            'file',
            'showForm',
            $element->getPersistedId(),
            false,
            $element->getConnectedFileType($propertyName),
        );
        if (!$fileElement instanceof fileElement) {
            return null;
        }
        $destinationFolder = (string)$element->getUploadedFilesPath($propertyName);

        $info = pathinfo($fileUrl);
        // "0" counts as no extension, as it always has
        if (in_array($info['extension'] ?? '', ['', '0'], true)) {
            $extension = self::EXTENSIONS_BY_MIME_TYPE[(string)mime_content_type($filePath)] ?? null;
            if ($extension !== null) {
                $originalFileName .= '.' . $extension;
            }
        }
        $fileElement->title = str_replace('_', ' ', ucfirst($info['filename']));
        $fileElement->structureName = $fileElement->title;
        $fileElement->file = (string)$fileElement->getPersistedId();
        $fileElement->fileName = $originalFileName;
        $fileElement->author = $fileAuthor ?? '';
        rename($filePath, $destinationFolder . $fileElement->file);
        $fileElement->persistElementData();

        $element->appendFileToList($fileElement, $propertyName);

        return $fileElement;
    }
}
