<?php

declare(strict_types=1);

namespace ZxArt\Releases\ItchIo;

use App\Users\CurrentUserService;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use RuntimeException;
use ZipArchive;
use structureManager;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ArchiveFileResolverService;
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
        private ItchIoPageParser $parser,
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
        // itch.io ties the download permission and the csrf token to the visitor's session
        $session = new CookieJar();
        $page = $this->openDownloadsPage($game, $session);
        $csrfToken = $page === null ? null : $this->parser->findCsrfToken($page);
        if ($page === null || $csrfToken === null) {
            return 0;
        }

        $releaseFileTypes = $this->archiveFileResolverService->getAllReleaseFileTypes();
        $knownHashes = $this->getReleaseHashes($prod);
        $added = 0;
        foreach ($this->parser->findUploads($page) as $upload) {
            if ($upload->nativeBuild) {
                continue;
            }
            $file = $this->downloadUpload($game, $upload, $csrfToken, $session, $releaseFileTypes);
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
     * The game page itself when it offers the files, otherwise the download
     * page its donation prompt leads to.
     */
    private function openDownloadsPage(ItchIoGameUrl $game, CookieJar $session): ?string
    {
        $gamePage = $this->get($game->gameUrl, $session);
        if ($gamePage === null || $this->parser->findUploads($gamePage) !== []) {
            return $gamePage;
        }
        $endpoint = $this->parser->findDownloadPageEndpoint($gamePage);
        $csrfToken = $this->parser->findCsrfToken($gamePage);
        if ($endpoint === null || $csrfToken === null) {
            return null;
        }
        $downloadPageUrl = $this->requestUrl($endpoint, $csrfToken, $session);
        return $downloadPageUrl === null ? null : $this->get($downloadPageUrl, $session);
    }

    /**
     * The file endpoint answers with a short-lived storage address. A game of a
     * new creator is quarantined by itch.io until reviewed; its visitors are
     * warned and may download anyway, which is what `bypass_quarantine` says.
     * An upload's label need not be its file name, so the type is only known
     * from the name the storage sends; the body is read only when that type is
     * a release type.
     *
     * @param list<string> $releaseFileTypes
     */
    private function downloadUpload(
        ItchIoGameUrl $game,
        ItchIoUpload $upload,
        string $csrfToken,
        CookieJar $session,
        array $releaseFileTypes,
    ): ?ItchIoFile {
        $endpoint = $game->getFileEndpoint($upload->id) . '?source=game_download&bypass_quarantine=true';
        $fileUrl = $this->requestUrl($endpoint, $csrfToken, $session);
        if ($fileUrl === null) {
            return null;
        }

        try {
            $fileResponse = $this->httpClient->get($fileUrl, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
                'stream' => true,
            ]);
            $name = $this->parser->findFileName($fileResponse->getHeaderLine('Content-Disposition')) ?? $upload->name;
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($extension, $releaseFileTypes, true)) {
                $fileResponse->getBody()->close();
                return null;
            }
            $file = new ItchIoFile($name, $fileResponse->getBody()->getContents());
            return $this->holdsReleaseFile($file, $releaseFileTypes) ? $file : null;
        } catch (GuzzleException|RuntimeException $exception) {
            $this->logger->error('ItchIoReleasesImporter::downloadUpload: ' . $endpoint . ' ' . $exception->getMessage());
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

    private function get(string $url, CookieJar $session): ?string
    {
        try {
            $response = $this->httpClient->get($url, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
                'cookies' => $session,
            ]);
        } catch (GuzzleException $exception) {
            $this->logger->error('ItchIoReleasesImporter::get: ' . $url . ' ' . $exception->getMessage());
            return null;
        }
        return (string)$response->getBody();
    }

    /**
     * Both endpoints the download needs answer a POST with `{url}`, or with
     * `{errors}` / a lightbox when they refuse.
     */
    private function requestUrl(string $url, string $csrfToken, CookieJar $session): ?string
    {
        try {
            $response = $this->httpClient->post($url, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
                'cookies' => $session,
                'form_params' => ['csrf_token' => $csrfToken],
            ]);
        } catch (GuzzleException $exception) {
            $this->logger->error('ItchIoReleasesImporter::requestUrl: ' . $url . ' ' . $exception->getMessage());
            return null;
        }
        $body = (string)$response->getBody();
        /** @var array{url?: string}|null $decoded */
        $decoded = json_decode($body, true);
        $answeredUrl = $decoded['url'] ?? null;
        if ($answeredUrl === null) {
            $this->logger->error('ItchIoReleasesImporter::requestUrl: no address from ' . $url . ' ' . $body);
        }
        return $answeredUrl;
    }
}
