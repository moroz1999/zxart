<?php

use App\Paths\PathsManager;
use ImageProcess\ImageProcess;

/**
 * @property ImageProcess $renderingEngine
 */
class imageProcessRendererPlugin extends rendererPlugin
{
    protected $exportOperation = null;
    protected $contentRead = false;
    protected $cachePath = false;

    public function init()
    {
        $configManager = $this->getService(ConfigManager::class);
        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $pathsManager = $this->getService(PathsManager::class);
        $this->cachePath = $pathsManager->getPath('imagesCache');
        $this->renderingEngine = new ImageProcess($this->cachePath);
        $defaultCachePermissions = $configManager->get('paths.defaultCachePermissions');
        $this->renderingEngine->setDefaultCachePermissions($defaultCachePermissions);
//        $this->renderingEngine->setImagesCaching(false);
//        $this->renderingEngine->setGammaCorrectionEnabled(false);
        $this->maxAge = 365 * 60 * 60 * 24;
        $this->httpResponse->setCacheControl('public');
        $this->preferredEncodings = ['identity'];
    }

    public function fetch()
    {
    }

    public function assign($attributeName, $value)
    {
        if ($attributeName === 'registerImage') {
            $this->renderingEngine->registerImage($value[0], $value[1]);
        } elseif ($attributeName === 'registerExport') {
            $filePath = $value[2] ?? '';
            $result = $this->renderingEngine->registerExport(
                $value[0],
                $value[1],
                $filePath !== '' ? $filePath : null,
                $value[3] ?? null,
                $value[4] ?? false
            );
            $this->exportOperation = $result;
        } elseif ($attributeName === 'registerFilter') {
            $this->renderingEngine->registerFilter(
                $value[0],
                $value[1] ?? null,
                $value[2] ?? null,
                $value[3] ?? null,
                $value[4] ?? null,
            );
        }
    }

    protected function getEtag()
    {
        return $this->exportOperation['parametersHash'];
    }

    protected function getLastModified()
    {
        $imageFilePath = $this->exportOperation['cacheFilePath'];
        if (is_file($imageFilePath)) {
            $this->lastModified = filemtime($imageFilePath);
        }
        return $this->lastModified;
    }

    protected function getContentLength()
    {
        $imageFilePath = $this->exportOperation['cacheFilePath'];
        if (is_file($imageFilePath)) {
            return filesize($imageFilePath);
        }
        return 0;
    }

    protected function getContentType()
    {
        $contentTypes = $this->requestHeadersManager->getAcceptedTypes();
        $imageType = $this->exportOperation['fileType'];
        $preferredOrder = false;
        if ($imageType === 'webp') {
            $preferredOrder = ['image/webp'];
        } elseif ($imageType === 'png') {
            $preferredOrder = ['image/png'];
        } elseif ($imageType === 'gif') {
            $preferredOrder = ['image/gif'];
        } elseif ($imageType === 'jpg' || $imageType === 'jpeg') {
            $preferredOrder = ['image/jpeg'];
        } elseif ($imageType === 'bmp') {
            $preferredOrder = ['image/x-bmp'];
        } elseif ($imageType === 'svg') {
            $preferredOrder = ['image/svg+xml'];
        }

        $selectedType = $this->selectHTTPParameter($preferredOrder, $contentTypes, '*/*');

        return $selectedType;
    }

    public function getContentDisposition()
    {
        return $this->contentDisposition ?: 'inline';
    }

    protected function renderContent()
    {
        $this->renderingEngine->executeProcess();
    }

    protected function getContentTextPart()
    {
        if (!$this->contentRead) {
            $this->contentRead = true;
            $imageFilePath = $this->exportOperation['cacheFilePath'];
            if (is_file($imageFilePath)) {
                return file_get_contents($imageFilePath);
            }
        }
        return false;
    }

    protected function compress($encoding)
    {
    }
}
