<?php

trait ConnectedIconsProviderTrait
{
    protected $connectedIconsIds;
    /**
     * @var genericIconElement[]
     */
    protected $connectedIcons;

    public function getConnectedIconsInfo()
    {
        $info = [];
        foreach ($this->getConnectedIcons() as $connectedIcon) {
            $item = [];
            $item['id'] = $connectedIcon->id;
            $item['title'] = $connectedIcon->getTitle();
            $item['select'] = true;
            $info[] = $item;
        }

        return $info;
    }

    /**
     * @return genericIconElement[]
     */
    public function getConnectedIcons()
    {
        if ($this->connectedIcons === null) {
            $this->connectedIcons = [];
            if ($iconIds = $this->getConnectedIconsIds()) {
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                foreach ($iconIds as &$iconId) {
                    if ($iconId && $iconElement = $structureManager->getElementById($iconId)) {
                        $this->connectedIcons[] = $iconElement;
                    }
                }
            }
        }
        return $this->connectedIcons;
    }

    public function getConnectedIconsIds()
    {
        if ($this->connectedIconsIds === null) {
            /**
             * @var linksManager $linksManager
             */
            $linksManager = $this->getService(linksManager::class);
            $this->connectedIconsIds = $linksManager->getConnectedIdList($this->id, $this->structureType . "Icon", "parent");
        }
        return $this->connectedIconsIds;
    }

    public function updateConnectedIcons($formIcons)
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);

        // check icon links
        if ($connectedIconsIds = $this->getConnectedIconsIds()) {
            foreach ($connectedIconsIds as &$connectedIconId) {
                if (!in_array($connectedIconId, $formIcons)) {
                    $linksManager->unLinkElements($this->id, $connectedIconId, $this->structureType . 'Icon');
                }
            }
        }
        foreach ($formIcons as $selectedIconId) {
            $linksManager->linkElements($this->id, $selectedIconId, $this->structureType . 'Icon');
        }
        $this->connectedIconsIds = null;
    }
}