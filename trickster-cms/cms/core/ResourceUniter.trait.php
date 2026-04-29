<?php

/**
 * Created by PhpStorm.
 * User: reneollino
 * Date: 24/10/14
 * Time: 16:41
 */
trait ResourceUniterTrait
{
    protected $contentRead = false;
    protected $fileName;
    protected $cacheFileName;
    protected $cacheFilePath;
    protected $cacheFileLastModTime = false;
    protected $cacheNeedsUpdating;
    protected $userAgentEngineType;
    protected $isGZipped = false;

    protected function getCacheFilePath()
    {
        if (!$this->cacheFilePath) {
            if (!is_dir($this->cachePath)) {
                mkdir($this->cachePath);
                $defaultCachePermissions = controller::getInstance()
                    ->getConfigManager()
                    ->get('paths.defaultCachePermissions');
                chmod($this->cachePath, $defaultCachePermissions);
            }
            $this->cacheFilePath = $this->cachePath . $this->getCacheFileName();
        }
        return $this->cacheFilePath;
    }

    protected function getContentType()
    {
        $contentTypes = $this->requestHeadersManager->getAcceptedTypes();

        $selectedType = $this->selectHTTPParameter($this->preferredOrder, $contentTypes, '*/*');
        return $selectedType;
    }

    public function assign($attributeName, $value)
    {
        if ($attributeName == 'fileName') {
            $this->fileName = $value;
        }
        if ($attributeName == 'cacheFileName') {
            $this->cacheFileName = $value;
        }
        if ($attributeName == 'resources' || $attributeName == 'cssResources') {
            $this->resources = $value;
        }
        if ($attributeName == 'useDataUri') {
            $this->useDataUri = $value;
        }
        if ($attributeName == 'userAgentEngineType') {
            $this->userAgentEngineType = $value;
        }
    }

    protected function getCacheFileName()
    {
        if (!$this->cacheFileName) {
            $this->generateCacheFileName();
        }
        return $this->cacheFileName;
    }

    protected function getCacheFileLastModTime()
    {
        if ($this->cacheFileLastModTime === false) {
            if (is_file($this->getCacheFilePath())) {
                $this->cacheFileLastModTime = filemtime($this->getCacheFilePath());
            } else {
                $this->cacheFileLastModTime = null;
            }
        }
        return $this->cacheFileLastModTime;
    }

    protected function getEtag()
    {
        return $this->getCacheFileName() . $this->getCacheFileLastModTime();
    }

    protected function getContentLength()
    {
        return strlen($this->contentText);
    }

    protected function compress($encoding)
    {
        if ($encoding == 'gzip') {
            $path = $this->getCacheFilePath() . '_gzip';
            if (!$this->isGZipped && ($this->cacheNeedsUpdating() || !is_file($path))) {
                $this->contentText = $this->gzip($this->contentText);
                file_put_contents($path, $this->contentText);
                $defaultCachePermissions = controller::getInstance()
                    ->getConfigManager()
                    ->get('paths.defaultCachePermissions');
                chmod($path, $defaultCachePermissions);
                $this->isGZipped = true;
            } else {
                $this->contentText = file_get_contents($path);
            }
        }
    }

    public function getContentDisposition()
    {
        return 'inline';
    }

    protected function getContentTextPart()
    {
        if (!$this->contentRead) {
            $this->contentRead = true;
            return $this->contentText;
        }
        return false;
    }
}