<?php

class publicReceiveParty extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param partyElement $structureElement
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if ($structureElement->structureName == '') {
                if ($structureElement->abbreviation) {
                    $structureElement->structureName = $structureElement->abbreviation;
                } elseif ($structureElement->title) {
                    $structureElement->structureName = $structureElement->title;
                }
            }
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            $structureElement->recalculate();

            $structureElement->persistElementData();
            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'abbreviation',
            'city',
            'country',
            'image',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

