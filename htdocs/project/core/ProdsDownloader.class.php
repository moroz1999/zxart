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
                if ($contents = file_get_contents($url)) {
                    file_put_contents($filePath, $contents);
                } else {
                    $this->logError('File downloading failed: ' . $url);
                }
            }
            return $filePath;
        }
        return false;
    }

    public function getFileContentsMd5($url)
    {
        if ($contents = $this->getFileContents($url)) {
            return md5($contents);
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
}