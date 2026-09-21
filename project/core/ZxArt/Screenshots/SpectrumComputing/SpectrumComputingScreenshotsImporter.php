<?php

declare(strict_types=1);

namespace ZxArt\Screenshots\SpectrumComputing;

use fileElement;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Logger;
use structureManager;
use ZxArt\Import\Services\ZxdbEntryIdsProvider;
use zxProdElement;

/**
 * Copies the loading and running screens of the prod's Spectrum Computing
 * entries into its screenshot gallery. Screens the gallery already holds are
 * skipped, so running it again only adds what appeared since.
 */
final readonly class SpectrumComputingScreenshotsImporter
{
    private const string ENTRY_PAGE_URL = SpectrumComputingScreensParser::SITE_URL . '/index.php?cat=96&id=';
    private const float TIMEOUT_SECONDS = 20.0;
    private const string USER_AGENT = 'zxart.ee screenshots import';

    public function __construct(
        private ZxdbEntryIdsProvider $zxdbEntryIdsProvider,
        private SpectrumComputingScreensParser $parser,
        private structureManager $structureManager,
        private Client $httpClient,
        private Logger $logger,
    ) {
    }

    /**
     * @return int number of screenshots added
     */
    public function import(zxProdElement $prod): int
    {
        $knownHashes = $this->getGalleryHashes($prod);
        $added = 0;
        foreach ($this->zxdbEntryIdsProvider->getEntryIds($prod->getId()) as $entryId) {
            $page = $this->download(self::ENTRY_PAGE_URL . $entryId);
            if ($page === null) {
                continue;
            }
            foreach ($this->parser->parse($page) as $screen) {
                $data = $this->download($screen->url);
                if ($data === null) {
                    continue;
                }
                $hash = md5($data);
                if (isset($knownHashes[$hash])) {
                    continue;
                }
                $knownHashes[$hash] = true;
                $this->addScreenshot($prod, $data, $screen->format);
                $added++;
            }
        }
        return $added;
    }

    /**
     * @return array<string, true>
     */
    private function getGalleryHashes(zxProdElement $prod): array
    {
        $hashes = [];
        $directory = (string)$prod->getUploadedFilesPath();
        foreach ($prod->getFilesList('connectedFile') as $fileElement) {
            $filePath = $directory . $fileElement->file;
            if (is_file($filePath)) {
                $hashes[(string)md5_file($filePath)] = true;
            }
        }
        return $hashes;
    }

    private function download(string $url): ?string
    {
        try {
            $response = $this->httpClient->get($url, [
                'timeout' => self::TIMEOUT_SECONDS,
                'headers' => ['User-Agent' => self::USER_AGENT],
            ]);
        } catch (GuzzleException $exception) {
            $this->logger->error('SpectrumComputingScreenshotsImporter::download: ' . $url . ' ' . $exception->getMessage());
            return null;
        }
        return (string)$response->getBody();
    }

    private function addScreenshot(zxProdElement $prod, string $data, SpectrumComputingScreenFormat $format): void
    {
        $fileElement = $this->structureManager->createElement(
            'file',
            'showForm',
            $prod->getFilesParentElementId(),
            false,
            $prod->getConnectedFileType('connectedFile')
        );
        if (!$fileElement instanceof fileElement) {
            return;
        }

        $fileElement->title = $prod->title;
        $fileElement->file = (string)$fileElement->getPersistedId();
        $fileElement->fileName = $fileElement->getPersistedId() . '.' . $format->value;
        $fileElement->persistElementData();

        file_put_contents((string)$prod->getUploadedFilesPath() . $fileElement->file, $data);
    }
}
