<?php

class receiveLanguage extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param languageElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            $structureElement->structureName = $structureElement->iso6393;
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            if (!is_null($structureElement->getDataChunk("backgroundImage")->originalName)) {
                $chunk = $structureElement->getDataChunk("backgroundImage");
                if ($chunk->width < 1024 && $chunk->height < 1024) {
                    $structureElement->patternBackground = 1;
                } else {
                    $structureElement->patternBackground = 0;
                }
                $structureElement->backgroundImage = $structureElement->id . "backgroundImage";
                $structureElement->backgroundImageOriginalName = $structureElement->getDataChunk("backgroundImage")->originalName;
            }
            if (!is_null($structureElement->getDataChunk("logoImage")->originalName)) {
                $structureElement->logoImage = $structureElement->id . "logoImage";
                $structureElement->logoImageOriginalName = $structureElement->getDataChunk("logoImage")->originalName;
            }
            $firstParent = $structureManager->getElementsFirstParent($structureElement->id);
            $structureElement->group = $firstParent->marker;

            $structureElement->persistElementData();
            $structureElement->setViewName('result');
            $controller->redirect($structureElement->URL);
        } else {
            $structureElement->executeAction("showForm");
        }
    }

    public function setValidators(&$validators)
    {
        $validators['structureName'][] = 'notEmpty';
        $validators['title'][] = 'notEmpty';
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'iso6391',
            'iso6393',
            'title',
            'hidden',
            'image',
            'backgroundImage',
            'logoImage',
        ];
    }
}


