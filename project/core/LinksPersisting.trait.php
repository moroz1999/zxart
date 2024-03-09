<?php

trait LinksPersistingTrait
{
    public function checkLinks($property, $linkType = null)
    {
        if ($linkType === null) {
            $linkType = $this->structureType . ucfirst($property);
        }
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService('linksManager');
        $linksIndex = $linksManager->getElementsLinksIndex($this->getId(), $linkType, 'child');
        foreach ($this->$property as $id) {
            if (!isset($linksIndex[$id])) {
                $linksManager->linkElements($id, $this->getId(), $linkType);
            }
            unset($linksIndex[$id]);
        }

        foreach ($linksIndex as $key => &$link) {
            $link->delete();
        }
    }
}