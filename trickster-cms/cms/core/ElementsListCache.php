<?php

class ElementsListCache
{
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var string
     */
    private $cacheId;

    /**
     * @var string
     */
    private $cacheKey;
    /**
     * @var int
     */
    private $cacheLifeTime;
    /**
     * @var structureManager
     */
    private $structureManager;
    private $idList;
    /**
     * @var structureElement[]
     */
    private ?array $elements;
    private bool $isLoaded = false;

    /**
     * @param Cache $cache
     */
    public function setCache($cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param mixed $cacheKey
     */
    public function setCacheKey($cacheKey): void
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @param int $cacheLifeTime
     */
    public function setCacheLifeTime($cacheLifeTime): void
    {
        $this->cacheLifeTime = $cacheLifeTime;
    }

    /**
     * @param int $cacheId
     */
    public function setCacheId($cacheId): void
    {
        $this->cacheId = $cacheId;
    }

    /**
     * @param structureManager $structureManager
     */
    public function setStructureManager($structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    public function load(): ?array
    {
        if (!$this->isLoaded) {
            $this->idList = $this->cache->get($this->cacheId . ':' . $this->cacheKey);
            $this->isLoaded = true;
            if ($this->idList !== null) {
                $this->elements = [];
                foreach ($this->idList as $id) {
                    if ($element = $this->structureManager->getElementById($id)) {
                        $this->elements[] = $element;
                    }
                }
            } else {
                $this->elements = null;
            }
        }

        return $this->elements;
    }

    public function save($elements): void
    {
        $this->elements = $elements;
        $this->idList = [];
        foreach ($this->elements as $element) {
            if ($element instanceof structureElement) {
                $id = $element->getPersistedId();
                $this->registerElementCacheKey($id, $this->cacheId . ':' . $this->cacheKey);
                $this->idList[] = $id;
            }
        }
        $this->registerElementCacheKey($this->cacheId, $this->cacheId . ':' . $this->cacheKey);
        $this->cache->set($this->cacheId . ':' . $this->cacheKey, $this->idList, $this->cacheLifeTime);
    }

    public function loaded(): bool
    {
        return !empty($this->elements);
    }

    protected function registerElementCacheKey($id, $key): void
    {
        if (!($keys = $this->cache->get($id . ':k'))) {
            $keys = [];
        }
        $keys[$key] = 1;
        $this->cache->set($id . ':k', $keys, 3600 * 24 * 7);
    }
}