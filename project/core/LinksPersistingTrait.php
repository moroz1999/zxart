<?php

trait LinksPersistingTrait
{
    public function checkLinks($property, $linkType = null): void
    {
        if ($linkType === null) {
            $linkType = $this->structureType . ucfirst($property);
        }
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $linksIndex = $linksManager->getElementsLinksIndex($this->getPersistedId(), $linkType, 'child');
        foreach ($this->$property as $id) {
            if (!isset($linksIndex[$id])) {
                $linksManager->linkElements($id, $this->getPersistedId(), $linkType);
            }
            unset($linksIndex[$id]);
        }

        foreach ($linksIndex as $key => &$link) {
            $link->delete();
            // the links manager still holds the deleted link for both ends
            $linksManager->resetElementsCacheById((int)$link->parentStructureId);
        }
        if ($linksIndex !== []) {
            $linksManager->resetElementsCacheById($this->getPersistedId());
        }
    }
}