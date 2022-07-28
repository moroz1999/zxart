<?php

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

    public function init()
    {
        $this->requestHeadersManager = $this->getService('requestHeadersManager');
        $this->httpResponse = httpResponse::getInstance();

        $this->renderingEngine = new Converter();
        if (!is_dir($this->getService('PathsManager')->getPath('zxCache'))) {
            mkdir(
                $this->getService('PathsManager')->getPath('zxCache'),
                $this->getService('ConfigManager')->get('paths.defaultCachePermissions'),
                true
            );
        }
        $this->renderingEngine->setCachePath($this->getService('PathsManager')->getPath('zxCache'));
        $this->renderingEngine->setCacheEnabled(true);
        $this->maxAge = 8 * 60 * 60 * 24;
        $this->setCacheControl('public');
        $this->preferredEncodings = ['identity'];
    }

    public function fetch()
    {
    }

    public function assign($attributeName, $value)
    {
        if ($attributeName == 'type') {
            $this->renderingEngine->setType($value);
            if ($value === 'hidden') {
                $this->renderingEngine->setCacheEnabled(false);
            }
        } elseif ($attributeName == 'filename') {
            $this->renderingEngine->setPath($value);
        } elseif ($attributeName == 'mode') {
            $this->renderingEngine->setGigascreenMode($value);
        } elseif ($attributeName == 'palette') {
            $this->renderingEngine->setPalette($value);
        } elseif ($attributeName == 'border') {
            $this->renderingEngine->setBorder($value);
        } elseif ($attributeName == 'zoom') {
            $this->renderingEngine->setZoom($value);
        } elseif ($attributeName == 'rotation') {
            $this->renderingEngine->setRotation($value);
        } elseif ($attributeName == 'fileContents') {
            $this->renderingEngine->setSourceFileContents($value);
        } elseif ($attributeName == 'cacheEnabled') {
            $this->renderingEngine->setCacheEnabled($value);
        }
    }

    public function getFileName()
    {
        if ($mime = $this->renderingEngine->getResultMime()) {
            if ($mime == 'image/png') {
                return 'image.png';
            } elseif ($mime == 'image/gif') {
                return 'image.gif';
            }
        }
        return false;
    }

    protected function getEtag()
    {
        $exportHash = $this->renderingEngine->getHash();
        return $exportHash;
    }

    protected function getContentLength()
    {
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

    protected function compress($encoding)
    {
    }

    public function getCacheFileName()
    {
        return $this->renderingEngine->getCacheFileName();
    }
}

