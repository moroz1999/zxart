<?php

use App\Paths\PathsManager;

class javascriptUniterRendererPlugin extends rendererPlugin
{
    use ResourceUniterTrait;
    protected $resources = null;
    protected $cachePath;
    protected $preferredOrder = ['application/javascript'];
    protected $encoding;

    public function init()
    {
        $pathsManager = $this->getService(PathsManager::class);
        $this->cachePath = $pathsManager->getPath('javascriptCache');
        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $this->maxAge = 365 * 60 * 60 * 24;
        $this->httpResponse->setCacheControl('public');
        $this->preferredEncodings = [
            'gzip',
            'deflate',
            'identity',
        ];
    }

    public function fetch()
    {
    }

    protected function generateCacheFileName()
    {
        $fileString = '';
        if (count($this->resources)) {
            foreach ($this->resources as &$file) {
                if (is_file($file)) {
                    $fileString .= $file;
                    $fileString .= filesize($file);
                    $fileString .= filemtime($file);
                }
            }
        }
        $this->cacheFileName = md5($fileString);
    }

    protected function cacheNeedsUpdating()
    {
        if ($this->cacheNeedsUpdating !== null) {
            return $this->cacheNeedsUpdating;
        }
        $path = $this->getCacheFilePath();
        if (!is_file($path) || !is_file($path . '_' . $this->encoding) || !$this->resources) {
            $this->cacheNeedsUpdating = true;
            return true;
        }

        foreach ($this->resources as &$resource) {
            if (is_file($resource) && $this->getCacheFileLastModTime() < filemtime($resource)) {
                $this->cacheNeedsUpdating = true;
                return true;
            }
        }
        $this->cacheNeedsUpdating = false;
        return false;
    }

    protected function renderContent()
    {
        if ($this->cacheNeedsUpdating()) {
            $this->contentText = $this->compileJs();
        } elseif (!($this->contentText = file_get_contents($this->getCacheFilePath()))) {
            $this->contentText = $this->compileJs();
        }
    }

    protected function compileJs()
    {
        $allFilesContent = "";
        foreach ($this->resources as $file) {
            if (is_file($file)) {
                $allFilesContent .= file_get_contents($file) . ";\n";
            } else {
                $this->logError('Javascript file is missing: ' . $file);
            }
        }
        if (!$this->debugMode) {
            try {
                $allFilesContent = \JShrink\Minifier::minify($allFilesContent, ['flaggedComments' => false]);
            } catch (Exception $e) {
                $this->logError('JS minifying failed: ' . $e->getMessage());
            }
        }

        $cachePermissions = $this->getService(ConfigManager::class)->get('paths.defaultCachePermissions');
        file_put_contents($this->getCacheFilePath(), $allFilesContent);
        chmod($this->getCacheFilePath(), $cachePermissions);

        return $allFilesContent;
    }
}
