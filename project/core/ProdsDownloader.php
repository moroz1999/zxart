<?php
declare(strict_types=1);

class ProdsDownloader extends errorLogger
{
    private float $lastRequestEndedAt = 0.0;
    private int $minRequestIntervalSeconds = 3;

    public function __construct(
        private readonly ConfigManager $configManager,
        private readonly PathsManager  $pathsManager,
    )
    {

    }

    public function getFileContents(string $url): ?string
    {
        $fileContents = null;
        if ($filePath = $this->getDownloadedPath($url)) {
            $contentOrFalse = file_get_contents($filePath);
            $fileContents = $contentOrFalse === false ? null : $contentOrFalse;
        }

        return $fileContents;
    }

    public function getDownloadedPath(string $url): string|false
    {
        if ($url) {
            $filePath = $this->getFilePath($url);
            if (!is_file($filePath)) {
                $this->downloadUrl($url, $filePath);
            }
            if (is_file($filePath)) {
                return $filePath;
            }
            return false;
        }
        return false;
    }

    public function moveFileContents(string $path, string $url): bool
    {
        $filePath = $this->getFilePath($url);
        if ($this->getFileContents($url)) {
            return rename($filePath, $path);
        }
        return false;
    }

    public function removeFile(string $url): bool
    {
        $filePath = $this->getFilePath($url);
        if (is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    protected function getFilePath(string $url): string|false
    {
        if ($url) {
            $path = parse_url($url, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $fileName = md5($url) . '.' . $extension;
            $cachePath = $this->pathsManager->getPath('uploadsCache');

            if (!is_dir($cachePath)) {
                if (!mkdir($cachePath, $this->configManager->get('paths.defaultCachePermissions'), true) && !is_dir($cachePath)) {
                    $this->logError('Failed to create cache directory: ' . $cachePath);
                    return false;
                }
            }
            return $cachePath . $fileName;
        }
        return false;
    }

    public function downloadUrl(string $url, string $destination): bool
    {
        $this->enforceRateLimit();

        $result = false;
        $filePointer = fopen($destination, 'wb+');
        if ($filePointer === false) {
            $this->logError('Failed to open destination for writing: ' . $destination);
            return false;
        }

        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $filePointer);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0');
        curl_setopt($ch, CURLOPT_REFERER, 'https://spectrumcomputing.co.uk/');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Encoding: identity',
            'Pragma: no-cache',
            'Cache-Control: no-cache',
        ]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $exec = curl_exec($ch);
        if ($exec === false) {
            $this->logError(curl_error($ch));
        } else {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode !== 200 && $httpcode !== 226) {
                if ($httpcode !== 429) {
                    $this->logError("Error downloading $url: HTTP $httpcode");
                }
                unlink($destination);
            } else {
                $this->logError("Succesfully downloaded $url");
                $result = true;
            }
        }
        curl_close($ch);
        fclose($filePointer);
        $this->lastRequestEndedAt = microtime(true);
        return $result;
    }

    private function enforceRateLimit(): void
    {
        if ($this->lastRequestEndedAt <= 0) {
            return;
        }
        $target = $this->lastRequestEndedAt + $this->minRequestIntervalSeconds;
        $remaining = $target - microtime(true);
        if ($remaining > 0) {
            sleep((int)ceil($remaining));
        }
    }
}