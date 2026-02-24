<?php

use App\Paths\PathsManager;
use ZxImage\Converter;

/**
 * Class zxScreenRendererPlugin
 *
 * @property Converter renderingEngine
 */
class zxScreenRendererPlugin extends rendererPlugin
{
    protected $binary = false;
    protected $contentRead = false;
    protected $cacheFileName = '';
    private $fileName = '';

    /**
     * @return void
     */
    public function init()
    {
        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = httpResponse::getInstance();

        $this->renderingEngine = new Converter();
        if (!is_dir($this->getService(PathsManager::class)->getPath('zxCache'))) {
            mkdir(
                $this->getService(PathsManager::class)->getPath('zxCache'),
                $this->getService(ConfigManager::class)->get('paths.defaultCachePermissions'),
                true
            );
        }
        $this->renderingEngine->setCachePath($this->getService(PathsManager::class)->getPath('zxCache'));
        $this->renderingEngine->setCacheEnabled(true);
        $this->maxAge = 365 * 60 * 60 * 24;
        $this->setCacheControl('public');
        $this->preferredEncodings = ['identity'];
    }

    /**
     * @return void
     */
    public function fetch()
    {
    }

    /**
     * @return void
     */
    public function assign($attributeName, $value)
    {
        if ($attributeName === 'type') {
            $this->renderingEngine->setType($value);
            if ($value === 'hidden') {
                $this->renderingEngine->setCacheEnabled(false);
            }
        } elseif ($attributeName === 'path') {
            $this->renderingEngine->setPath($value);
        } elseif ($attributeName === 'mode') {
            $this->renderingEngine->setGigascreenMode($value);
        } elseif ($attributeName === 'palette') {
            $this->renderingEngine->setPalette($value);
        } elseif ($attributeName === 'border') {
            $this->renderingEngine->setBorder($value);
        } elseif ($attributeName === 'zoom') {
            $this->renderingEngine->setZoom($value);
        } elseif ($attributeName === 'rotation') {
            $this->renderingEngine->setRotation($value);
        } elseif ($attributeName === 'fileContents') {
            $this->renderingEngine->setSourceFileContents($value);
        } elseif ($attributeName === 'cacheEnabled') {
            $this->renderingEngine->setCacheEnabled($value);
        } elseif ($attributeName === 'fileName') {
            $this->fileName = $value;
        } elseif ($attributeName === 'cacheFileName') {
            $this->renderingEngine->setCacheFileName($value);
        }
    }

    /**
     * @return false|string
     */
    public function getFileName()
    {
        $fileName = $this->fileName ? $this->fileName : 'image';
        if ($mime = $this->renderingEngine->getResultMime()) {
            if ($mime === 'image/png') {
                return $fileName . '.png';
            } elseif ($mime === 'image/gif') {
                return $fileName . '.gif';
            }
        }
        return false;
    }

    protected function getEtag()
    {
        return $this->renderingEngine->getHash();
    }

    protected function getLastModified()
    {
        $imageFilePath = $this->renderingEngine->getCacheFileName();
        if (file_exists($imageFilePath)) {
            $this->lastModified = filemtime($imageFilePath);
        }
        return $this->lastModified;
    }

    /**
     * @return false|int
     */
    protected function getContentLength()
    {
        if ($this->binary) {
            return strlen($this->binary);
        }
        $imageFilePath = $this->renderingEngine->getCacheFileName();
        if (file_exists($imageFilePath)) {
            return filesize($imageFilePath);
        }
        return false;
    }

    protected function getContentType()
    {
        return $this->renderingEngine->getResultMime();
    }

    public function getContentDisposition()
    {
        return $this->contentDisposition;
    }

    /**
     * @return void
     */
    protected function renderContent()
    {
        $this->binary = $this->renderingEngine->getBinary();
    }

    protected function getContentTextPart()
    {
        if (!$this->contentRead) {
            $this->contentRead = true;
            return $this->binary;
        }
        return false;
    }

    /**
     * @return void
     */
    protected function compress($encoding)
    {
    }

    public function getCacheFileName()
    {
        return $this->renderingEngine->getCacheFileName();
    }
}


