<?php

trait PartyElementProviderTrait
{
    use CacheOperatingElement;

    /**
     * @var partyElement|null
     */
    private $partyElement = null;

    /**
     * @return partyElement|null
     */
    public function getPartyElement()
    {
        if ($this->partyElement === null) {
            $cache = $this->getElementsListCache('p', 60 * 60 * 24);
            if (($parties = $cache->load()) === null) {
                if ($partyId = $this->getPartyId()) {
                    $structureManager = $this->getService('structureManager');
                    $partyElement = $structureManager->getElementById($partyId);
                    if ($partyElement instanceof partyElement) {
                        $this->partyElement = $partyElement;
                    }
                }
                $cache->save([$this->partyElement]);
            } else {
                $partyElement = reset($parties);
                if ($partyElement instanceof partyElement) {
                    $this->partyElement = $partyElement;
                }
            }
        }
        return $this->partyElement;
    }

    abstract public function getPartyId();
}
