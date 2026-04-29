<?php

class ConnectedElementsDataChunk extends DataChunk implements ElementHolderInterface, ExtraDataHolderDataChunkInterface
{
    use ElementHolderDataChunkTrait;

    protected $ids;
    protected $role = 'parent';
    protected $linkType;
    protected ?string $order = null;

    public function setFormValue($value)
    {
        $value = (array)$value;
        $this->formValue = $this->loadElements($value);
    }

    public function getStorageValue()
    {
        if ($this->storageValue === null) {
            $this->loadStorageValue();
        }
        return $this->storageValue;
    }

    public function setExternalValue($value)
    {
        $this->formValue = null;
        $this->displayValue = null;
        $this->storageValue = array_map(static fn(structureElement $item): int => $item->getPersistedId(), $value);
    }

    protected function loadStorageValue()
    {
        $this->storageValue = [];

        if ($ids = $this->getIds()) {
            $this->storageValue = $ids;
        }
    }

    protected function getIds()
    {
        if ($this->ids === null) {
            $this->ids = [];
            $cache = $this->getService(Cache::class);
            $keyName = $this->structureElement->id . ':ce' . $this->linkType . $this->role;
            $value = $cache->get($keyName);
            if ($value === null) {
                /**
                 * @var linksManager $linksManager
                 */
                if ($linksManager = $this->getService(linksManager::class)) {
                    if ($this->ids = $linksManager->getConnectedIdList(
                        $this->structureElement->id,
                        $this->linkType,
                        $this->role
                    )) {
                        $cache->set($keyName, $this->ids, 3600 * 24);
                        $cacheIds = [...$this->ids, $this->structureElement->id];
                        foreach ($cacheIds as $id) {
                            $this->registerCacheKey($cache, $id, $keyName);
                        }

                        return $this->ids;
                    }
                }
            } else {
                $this->ids = $value;
            }
        }
        return $this->ids;
    }

    protected function registerCacheKey(Cache $cache, string|int $id, string $key): void
    {
        if (($keys = $cache->get($id . ':k')) === null) {
            $keys = [];
        }
        $keys[$key] = 1;
        $cache->set($id . ':k', $keys, 3600 * 24 * 7);
    }

    public function convertFormToStorage()
    {
        $this->storageValue = [];
        foreach ($this->formValue as $element) {
            $this->storageValue[] = $element->id;
        }
        $this->displayValue = $this->formValue;
    }

    public function convertStorageToDisplay()
    {
        if ($this->storageValue === null) {
            $this->loadStorageValue();
        }
        $this->displayValue = $this->loadElements($this->storageValue);
    }

    public function convertStorageToForm()
    {
        if ($this->storageValue === null) {
            $this->loadStorageValue();
        }
        $this->formValue = $this->loadElements($this->storageValue);
    }

    protected function loadElements($ids)
    {
        $elements = [];
        if (is_array($ids)) {
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            $orders = [];
            $order = $this->order ?? null;
            foreach ($ids as $id) {
                if ($element = $structureManager->getElementById($id)) {
                    $elements[] = $element;
                    if ($order) {
                        $orders[] = $element->$order;
                    }
                }
            }
            if ($orders !== []) {
                array_multisort($orders, SORT_ASC, $elements);
            }
        }
        return $elements;
    }

    public function persistExtraData()
    {
        if ($this->storageValue === null) {
            //this chunk wasn't modified at all, no need to load it and save it again.
            return;
        }
        /**
         * @var linksManager $linksManager
         */
        if ($linksManager = $this->getService(linksManager::class)) {
            if ($this->structureElement) {
                $linksIndex = $linksManager->getElementsLinksIndex($this->structureElement->getPersistedId(), $this->linkType, $this->role);
                foreach ($this->storageValue as $connectedId) {
                    if (!isset($linksIndex[$connectedId])) {
                        if ($this->role === 'child') {
                            $linksManager->linkElements($connectedId, $this->structureElement->getPersistedId(), $this->linkType);
                        } else {
                            $linksManager->linkElements($this->structureElement->getPersistedId(), $connectedId, $this->linkType);
                        }
                    }
                    unset($linksIndex[$connectedId]);
                }
                foreach ($linksIndex as $link) {
                    $link->delete();
                }
            }
        }
    }

    public function deleteExtraData()
    {
        /**
         * @var linksManager $linksManager
         */
        if ($linksManager = $this->getService(linksManager::class)) {
            $linksIndex = $linksManager->getElementsLinksIndex($this->structureElement->getPersistedId(), $this->linkType, $this->role);
            foreach ($linksIndex as $key => &$link) {
                $link->delete();
            }
        }
    }

    public function copyExtraData($oldValue, $oldId, $newId)
    {
        /**
         * @var linksManager $linksManager
         */
        if ($linksManager = $this->getService(linksManager::class)) {
            $ids = $linksManager->getConnectedIdList($oldId, $this->linkType, $this->role);
            foreach ($ids as $id) {
                if ($this->role === 'child') {
                    $linksManager->linkElements($id, $newId, $this->linkType);
                } else {
                    $linksManager->linkElements($newId, $id, $this->linkType);
                }
            }
        }
    }
}