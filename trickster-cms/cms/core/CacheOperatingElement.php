<?php

trait CacheOperatingElement
{
	/**
	 * @var Cache
	 */
	protected $cache;

	public function getCache(): Cache
	{
		if ($this->cache === null) {
			$this->cache = $this->getService(Cache::class);
		}
		return $this->cache;
	}

	/**
	 * @param string $key
     */
	protected function getElementsListCache($key, $cacheLifeTime): ElementsListCache
    {
		$list = new ElementsListCache();
		$list->setCacheId($this->getId());
		$list->setCacheKey($key);
		$list->setCacheLifeTime($cacheLifeTime);
		$list->setCache($this->getCache());
		$list->setStructureManager($this->getService('structureManager'));
		return $list;
	}

	protected function getCacheKey($key)
	{
		return $this->getCache()->get($this->id . ':' . $key);
	}

	protected function setCacheKey($key, $value, $lifeTime): void
    {
		$this->registerCacheKey($this->id . ':' . $key);
		$this->getCache()->set($this->id . ':' . $key, $value, $lifeTime);
	}

	protected function registerCacheKey($key): void
    {
		if (($keys = $this->getCache()->get($this->id . ':k')) === null) {
			$keys = [];
		}
		$keys[$key] = 1;
		$this->getCache()->set($this->id . ':k', $keys, 3600 * 24 * 7);
	}

	protected function deleteCache($key): null
    {
		return $this->getCache()->delete($this->id . ':' . $key);
	}
}
