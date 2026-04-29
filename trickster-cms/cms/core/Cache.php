<?php

use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Cache extends errorLogger
{
    /**
     * @var \fluxbb\cache\Cache
     */
    protected $cache;
    protected $cachePath;
    protected $cachePrefix = '';
    protected $enabled = false;
    protected $writing = true;
    protected $deleting = true;
    protected $reading = true;
    /**
     * @var ConfigManager
     */
    protected $configManager;

    public function __construct()
    {
        $controller = controller::getInstance();
        $this->configManager = $controller->getConfigManager();
    }

    public function enable($reading = true, $writing = true, $deleting = true)
    {
        if ($this->enabled = $this->configManager->get('cache.enabled')) {
            if ($prefix = $this->configManager->get('cache.prefix')) {
                $this->cachePrefix = $prefix . ':';
            }
            $this->prepareCache($this->configManager->get('cache.driver'));

            if (!$this->cache) {
                $this->enabled = false;
            }

            $this->reading = $reading;
            $this->writing = $writing && !((new CrawlerDetect())->isCrawler());
            $this->deleting = $deleting;
        }
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    protected function prepareCache($driver)
    {
        try {
            $controller = controller::getInstance();
            $pathsManager = $controller->getPathsManager();
            switch ($driver) {
                case 'OpcacheFile':
                    $this->cachePath = $pathsManager->getPath('Cache');
                    if (!is_dir($this->cachePath)) {
                        mkdir($this->cachePath, $this->configManager->get('paths.defaultCachePermissions'), true);
                    }
                    $this->cache = \fluxbb\cache\Cache::load($driver, [
                        'dir' => $pathsManager->getPath('temporary') . 'cache/',
                        'suffix' => '.php',
                    ], 'OpcacheSerialize');
                    break;
                case 'File':
                    $this->cachePath = $pathsManager->getPath('Cache');
                    if (!is_dir($this->cachePath)) {
                        mkdir($this->cachePath, $this->configManager->get('paths.defaultCachePermissions'), true);
                    }
                    $this->cache = \fluxbb\cache\Cache::load($driver, [
                        'dir' => $pathsManager->getPath('temporary') . 'cache/',
                    ], 'Serialize');
                    break;
                case 'Redis':
                    if ($redisConfig = $this->configManager->getConfig('redis')) {
                        $this->cache = fluxbb\cache\Cache::load($driver, [
                            'host' => $redisConfig->get('host'),
                            'port' => $redisConfig->get('port'),
                            'password' => $redisConfig->get('pass'),
                        ], 'Serialize');
                    }
                    break;
            }
        } catch (Exception $exception) {
            $this->logError($exception->getMessage());
        }
    }

    public function get($key, $forceReading = false): mixed
    {
        if ($this->enabled && ($this->reading || $forceReading)) {
            try {
                $value = $this->cache->get($this->cachePrefix . $key);
                if ($value === \fluxbb\cache\Cache::NOT_FOUND) {
                    return null;
                } else {
                    return $value;
                }
            } catch (\fluxbb\cache\Exception $exception) {
                $this->logError($exception->getMessage());
            }
        }
        return null;
    }

    public function set($key, $value, $ttl = 0): void
    {
        if ($this->enabled && $this->writing) {
            try {
                $this->cache->set($this->cachePrefix . $key, $value, $ttl);
            } catch (\fluxbb\cache\Exception $exception) {
            }
        }
    }

    public function clear()
    {
        if ($this->enabled && $this->deleting) {
            try {
                $this->cache->clear();
            } catch (\fluxbb\cache\Exception $exception) {
            }
        }
        return null;
    }

    public function delete($key)
    {
        if ($this->enabled && $this->deleting) {
            try {
                $this->cache->delete($this->cachePrefix . $key);
            } catch (\fluxbb\cache\Exception $exception) {
            }
        }
        return null;
    }

    public function clearKeysByType($id, $structureType)
    {
        if ($this->enabled && $this->deleting) {
            if ($keys = $this->configManager->get('cachekeys.' . $structureType)) {
                foreach ($keys as $key) {
                    $this->delete($id . ':' . $key);
                }
            }
        }
    }
}
