<?php

use App\Paths\PathsManager;

trait StandardApplicationCacheTrait
{
    protected $cacheFileName;

    public abstract function getCacheFilename();

    public abstract function canServeCache();

    public abstract function getContentType();

    public abstract function getApplicationName();

    public abstract function getCacheExpirationTime();

    protected function getCachePath()
    {
        return $this->getService(PathsManager::class)->getPath('appCache') . $this->getApplicationName() . '/';
    }

    public function cacheExists()
    {
        return file_exists($this->getCachePath() . $this->getCacheFilename());
    }

    public function createCache($content)
    {
        $cachePath = $this->getCachePath();
        $permissions = $this->getService(ConfigManager::class)->get('paths.defaultCachePermissions');
        if (!is_dir($cachePath)) {
            mkdir($cachePath, $permissions, true);
        }
        file_put_contents($cachePath . $this->getCacheFilename(), $content);
        chmod($cachePath . $this->getCacheFilename(), $permissions);
    }

    public function clearCache()
    {
        $cachePath = $this->getCachePath();

        if ($this->cacheExists()) {
            unlink($cachePath . $this->getCacheFilename());
        }
        $gzippedFile = $cachePath . $this->getCacheFilename() . '_gzip';
        if (file_exists($gzippedFile)) {
            unlink($gzippedFile);
        }
    }

    public function serveCache()
    {
        if ($this->cacheExists()) {
            $renderer = renderer::createInstance('applicationCache');
            $renderer->cacheFileName = $this->getCacheFilename();
            $renderer->setPreferedOrder($this->getContentType());
            $renderer->setCachePath($this->getCachePath());
            $renderer->display();
        }
    }
}
