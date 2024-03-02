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
    public function setConfigManager($configManager)
    {
        $this->configManager = $configManager;
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

    public function moveFileContents($path, $url)
    {
        $filePath = $this->getFilePath($url);
        if ($this->getFileContents($url)) {
            return rename($filePath, $path);
        }
        return false;
    }

    public function removeFile($url)
    {
        $filePath = $this->getFilePath($url);
        if (is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    protected function getFilePath($url)
    {
        if ($url) {
            $md5 = md5($url);
            $folder = $this->configManager->get('paths.uploadsCache');
            if (!is_dir($folder)) {
                mkdir($folder, $this->configManager->get('paths.defaultCachePermissions'), true);
            }
            return $folder . $md5;
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
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0');
        curl_setopt($ch, CURLOPT_REFERER, 'https://spectrumcomputing.co.uk/');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (curl_exec($ch) === false) {
            $this->logError(curl_error($ch));
        } else {
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