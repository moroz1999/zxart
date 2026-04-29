<?php

trait GenericIconProviderTrait
{

    protected $genericIconsList;
    protected $connectedGenericIconsId;

    /**
     * @return array
     */
    public function getGenericIconsList(): array
    {
        if (empty($this->genericIconsList)) {
            $this->setGenericIconsList();
        }
        return $this->genericIconsList;
    }

    protected function setGenericIconsList(): void
    {
        $genericIcons = $this->getGenericIcons();
        if (!empty($genericIcons)) {
            $iconsList = [];
            $connectedIconsIds = $this->getConnectedGenericIconsId();
            foreach ($genericIcons as $genericIcon) {
                $item = [];
                $item['id'] = $genericIcon->id;
                $item['title'] = $genericIcon->getTitle();
                $item['select'] = $this->isConnectedIcon($genericIcon->id);
                $iconsList[] = $item;
            }
        }
        $this->genericIconsList = $iconsList;
    }

    /**
     * @return array
     */
    protected function getConnectedGenericIconsId(): array
    {
        if (empty($this->connectedGenericIconsId)) {
            $linkType = 'genericIcon' . ucfirst($this->structureType);
            $linksManager = $this->getService(linksManager::class);
            $this->connectedGenericIconsId = $linksManager->getConnectedIdList($this->id, $linkType);
        }
        return $this->connectedGenericIconsId;
    }

    /**
     * @return array
     */
    protected function getGenericIcons(): array
    {
        $genericIcons = false;
        $structureManager = $this->getService('structureManager');
        if (!empty($structureManager)) {
            $genericIcons = $structureManager->getElementsByType('genericIcon');
        }
        return $genericIcons;
    }

    /**
     * @param number $id
     * @return bool
     */
    protected function isConnectedIcon(int $id): bool
    {
        $isConnected = false;
        if (!empty($id)) {
            $connectedIconsIds = $this->getConnectedGenericIconsId();
            $isConnected = in_array($id, $connectedIconsIds);
        }
        return $isConnected;
    }
}