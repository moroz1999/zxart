<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use App\Users\CurrentUserService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use RuntimeException;
use ZipArchive;
use structureManager;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ArchiveFileResolverService;
use ZxArt\Shared\Http\ContentDisposition;
use zxProdElement;
use zxReleaseElement;

/**
 * Copies the release files of the prod's itch.io game into new releases, one per
 * file, as original releases of the prod's year. Only files of a type some
 * machine is released in are taken — native PC, Mac and Android builds never are,
 * even when packed in an archive, nor a ZIP holding no such file (maps, manuals,
 * artwork) — and a file one of the prod's releases
 * already holds is skipped, so running it again only adds what appeared since.
 */
final readonly class ItchIoReleasesImporter
{
    private const float TIMEOUT_SECONDS = 60.0;
    private const string USER_AGENT = 'zxart.ee releases import';
    private const string ZIP_EXTENSION = 'zip';

    public function __construct(
        private ItchIoDownloads $downloads,
        private ArchiveFileResolverService $archiveFileResolverService,
        private structureManager $structureManager,
        private CurrentUserService $currentUserService,
        private Client $httpClient,
        private Logger $logger,
    ) {
    }

    /**
     * @return int number of releases added
     */
    public function import(zxProdElement $prod): int
    {
        $game = ItchIoGameUrl::tryFrom($prod->externalLink);
        if ($game === null) {
            return 0;
        }
        try {
            $page = $this->downloads->open($game);
        } catch (ItchIoUnavailableException $exception) {
            $this->logger->error('ItchIoReleasesImporter::import: ' . $exception->getMessage());
            return 0;
        }

        $releaseFileTypes = $this->archiveFileResolverService->getAllReleaseFileTypes();
        $knownHashes = $this->getReleaseHashes($prod);
        $added = 0;
        foreach ($page->uploads as $upload) {
            if ($upload->nativeBuild) {
                continue;
            }
            $file = $this->downloadUpload($page, $upload, $releaseFileTypes);
            if ($file === null) {
                continue;
            }
            $hash = md5($file->data);
            if (isset($knownHashes[$hash])) {
                continue;
            }
            $knownHashes[$hash] = true;
            $this->addRelease($prod, $upload, $file);
            $added++;
        }
        return $added;
    }

    /**
     * An upload's label need not be its file name, so the type is only known
     * from the name the storage sends; the body is read only when that type is
     * a release type.
     *
     * @param list<string> $releaseFileTypes
     */
    private function downloadUpload(ItchIoDownloadsPage $page, ItchIoUpload $upload, array $releaseFileTypes): ?ItchIoFile
    {
        try {
            $fileUrl = $this->downloads->fileUrl($page, $upload->id);
            $fileResponse = $this->httpClient->get($fileUrl, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
                'stream' => true,
            ]);
            $name = ContentDisposition::fileName($fileResponse->getHeaderLine('Content-Disposition')) ?? $upload->name;
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, $releaseFileTypes, true)) {
                $fileResponse->getBody()->close();
                return null;
            }
            $file = new ItchIoFile($name, $fileResponse->getBody()->getContents());
            return $this->holdsReleaseFile($file, $releaseFileTypes) ? $file : null;
        } catch (GuzzleException|RuntimeException $exception) {
            $this->logger->error('ItchIoReleasesImporter::downloadUpload: ' . $page->game->gameUrl . ' ' . $upload->id . ' ' . $exception->getMessage());
            return null;
        }
    }

    /**
     * A ZIP is a release only when it packs a release file of its own; other
     * archives cannot be looked into here and are taken as they are.
     *
     * @param list<string> $releaseFileTypes
     */
    private function holdsReleaseFile(ItchIoFile $file, array $releaseFileTypes): bool
    {
        if (strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) !== self::ZIP_EXTENSION) {
            return true;
        }
        $packedTypes = array_diff($releaseFileTypes, [self::ZIP_EXTENSION]);
        $temporaryPath = (string)tempnam(sys_get_temp_dir(), 'itchio');
        file_put_contents($temporaryPath, $file->data);
        $zip = new ZipArchive();
        $holdsReleaseFile = false;
        if ($zip->open($temporaryPath) === true) {
            for ($index = 0; $index < $zip->numFiles && !$holdsReleaseFile; $index++) {
                $extension = strtolower(pathinfo((string)$zip->getNameIndex($index), PATHINFO_EXTENSION));
                $holdsReleaseFile = in_array($extension, $packedTypes, true);
            }
            $zip->close();
        }
        unlink($temporaryPath);
        return $holdsReleaseFile;
    }

    /**
     * @return array<string, true>
     */
    private function getReleaseHashes(zxProdElement $prod): array
    {
        $hashes = [];
        foreach ($prod->getReleasesList() as $release) {
            $filePath = $release->getFilePath();
            if ($release->file !== '' && is_file($filePath)) {
                $hashes[(string)md5_file($filePath)] = true;
            }
        }
        return $hashes;
    }

    /**
     * A label the creator wrote ("ZX Spectrum English .tap and .z80 files v1.2")
     * says more than the file name and becomes the title; a label that is only
     * the file name is used without its extension.
     */
    private function addRelease(zxProdElement $prod, ItchIoUpload $upload, ItchIoFile $file): void
    {
        $release = $this->structureManager->createElement('zxRelease', 'show', $prod->getId());
        if (!$release instanceof zxReleaseElement) {
            return;
        }

        $release->title = $upload->name === $file->name ? pathinfo($file->name, PATHINFO_FILENAME) : $upload->name;
        $release->structureName = $release->title;
        $release->year = $prod->year;
        $release->releaseType = ReleaseTypes::original->value;
        $release->dateAdded = time();
        $release->userId = (int)$this->currentUserService->getCurrentUser()->id;
        $release->file = (string)$release->getPersistedId();
        $release->fileName = $file->name;
        $release->parsed = 0;
        file_put_contents($release->getFilePath(), $file->data);
        $release->persistElementData();

        $release->updateFileStructure();
    }
}
