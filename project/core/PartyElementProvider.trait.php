<?php

trait PartyElementProviderTrait
{
    use CacheOperatingElement;

    private $partyElement;

    public function getPartyElement()
    {
        if ($this->partyElement === null) {
            $this->partyElement = false;

            $cache = $this->getElementsListCache('p', 60 * 60 * 24);
            if (($parties = $cache->load()) === false) {
                if ($partyId = $this->getPartyId()) {
                    $structureManager = $this->getService('structureManager');
                    $this->partyElement = $structureManager->getElementById($partyId);
                }
                $cache->save([$this->partyElement]);
            } else {
                $this->partyElement = reset($parties);
            }
        }
        return $this->partyElement;
    }

    abstract public function getPartyId();
}