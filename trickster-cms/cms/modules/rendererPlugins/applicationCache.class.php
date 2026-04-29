<?php

use App\Paths\PathsManager;

class applicationCacheRendererPlugin extends rendererPlugin
{
    protected $contentRead = false;
    protected $contentText = null;
    public $cacheFileName = '';
    protected $cacheControl = 'no-cache';
    protected $preferedOrder = 'text/html';
    protected $cachePath;

    public function init()
    {
        $pathsManager = $this->getService(PathsManager::class);
        $this->cachePath = $pathsManager->getPath('appCache');

        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $this->httpResponse->setCharset('UTF-8');
        $this->preferredEncodings = [
            'gzip',
            'deflate',
            'identity',
        ];
        $this->maxAge = 60 * 5;
    }

    /**
     * @param mixed $cachePath
     */
    public function setCachePath($cachePath)
    {
        $this->cachePath = $cachePath;
    }

    public function setMaxAge($age)
    {
        $this->maxAge = $age;
    }

    public function setPreferedOrder($order)
    {
        $this->preferedOrder = $order;
    }

    public function fetch()
    {
        $this->renderContent();
        return $this->contentText;
    }

    public function assign($attributeName, $value)
    {
    }

    protected function getEtag()
    {
        $this->renderContent();
        return md5($this->contentText);
    }

    protected function getContentLength()
    {
        return strlen($this->contentText);
    }

    protected function getContentTextPart()
    {
        if (!$this->contentRead) {
            header('X-AppCache: ON');
            $this->contentRead = true;
            return $this->contentText;
        }
        return false;
    }

    protected function getContentType()
    {
        $contentTypes = $this->requestHeadersManager->getAcceptedTypes();

        $preferredOrder = [
            $this->preferedOrder,
        ];
        $selectedType = $this->selectHTTPParameter($preferredOrder, $contentTypes, '*/*');

        $userAgent = $this->requestHeadersManager->getUserAgent();
        $userAgentVersion = $this->requestHeadersManager->getUserAgentVersion();

        if ($userAgent == 'MSIE' && $userAgentVersion < 9) {
            return $this->preferedOrder;
        } else {
            return $selectedType;
        }
    }

    public function getContentDisposition()
    {
        return 'inline';
    }

    protected function renderContent()
    {
        if ($this->contentText === null) {
            $this->contentText = file_get_contents($this->cachePath . $this->cacheFileName);
        }
        return $this->contentText;
    }

    protected function compress($encoding)
    {
        if ($encoding == 'gzip') {
            if (!file_exists($this->cachePath . $this->cacheFileName . '_gzip')) {
                file_put_contents($this->cachePath . $this->cacheFileName . '_gzip', $this->gzip($this->contentText));
                chmod($this->cachePath . $this->cacheFileName . '_gzip', $this->getService(ConfigManager::class)
                    ->get('paths.defaultCachePermissions'));
            }
            $this->contentText = file_get_contents($this->cachePath . $this->cacheFileName . '_gzip');
        }
    }
}
