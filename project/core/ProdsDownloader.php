<?php

class ProdsDownloader extends errorLogger
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager($configManager): void
    {
        $this->configManager = $configManager;
    }

    /**
     * @var PathsManager
     */
    protected $pathsManager;

    public function setPathsManager(PathsManager $pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    public function getFileContents($url)
    {
        if ($filePath = $this->getDownloadedPath($url)) {
            $contents = file_get_contents($filePath);
        }

        return $contents;
    }

    public function getDownloadedPath($url)
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

    public function moveFileContents($path, $url): bool
    {
        $filePath = $this->getFilePath($url);
        if ($this->getFileContents($url)) {
            return rename($filePath, $path);
        }
        return false;
    }

    public function removeFile($url): bool
    {
        $filePath = $this->getFilePath($url);
        if (is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    /**
     * @return false|string
     */
    protected function getFilePath($url): string|false
    {
        if ($url) {
            $md5 = md5($url);
            $cachePath = $this->pathsManager->getPath('uploadsCache');

            if (!is_dir($cachePath)) {
                mkdir($cachePath, $this->configManager->get('paths.defaultCachePermissions'), true);
            }
            return $cachePath . $md5;
        }
        return false;
    }

    public function downloadUrl(string $url, string $destination): bool
    {
        $result = false;
        $fp = fopen($destination, 'wb+');

        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:127.0) Gecko/20100101 Firefox/127.0');
        curl_setopt($ch, CURLOPT_REFERER, 'https://spectrumcomputing.co.uk/');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept-Encoding: identity',
            'Pragma: no-cache',
            'Cache-Control: no-cache'
        ]);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $exec = curl_exec($ch);
        if ($exec === false) {
            $this->logError(curl_error($ch));
        } else {
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode !== 200) {
                $this->logError("Ошибка при скачивании $url: HTTP статус $httpcode");
                unlink($destination);
            } else {
                $result = true;
            }
        }
        curl_close($ch);
        fclose($fp);
        sleep(5);
        return $result;
    }
}