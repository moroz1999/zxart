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
            return $filePath;
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
        $fp = fopen($destination, 'w+');

        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:124.0) Gecko/20100101 Firefox/124.0');
        curl_setopt($ch, CURLOPT_REFERER, 'https://spectrumcomputing.co.uk/');
        curl_setopt($ch, CURLOPT_HEADER, false);

        $exec = curl_exec($ch);
        if ($exec === false) {
            $this->logError(curl_error($ch));
        } else {
            $test = curl_getinfo($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpcode != 200) {
                $this->logError("Ошибка при скачивании: HTTP статус $httpcode");
                fclose($fp);
                unlink($destination);
                curl_close($ch);
            } else {
                $result = true;
            }
        }
        curl_close($ch);
        fclose($fp);
        return $result;
    }
}