<?php

trait CategoryElementsProviderTrait
{
    private $partyElement;

    public function getPartyElement()
    {
        if ($this->partyElement === null) {
            $this->partyElement = false;
            if ($this->getPartyId() != '0') {
                $structureManager = $this->getService('structureManager');
                $this->partyElement = $structureManager->getElementById(
                    $this->getPartyId()
                );
            }
        }
        return $this->partyElement;
    }

    abstract public function getPartyId();
}