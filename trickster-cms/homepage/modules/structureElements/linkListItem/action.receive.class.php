<?php

class receiveLinkListItem extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param linkListItemElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            $structureElement->structureName = $structureElement->title;
            if ($structureElement->getDataChunk("image")->originalName) {
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            $additionalImages = [
                'secondaryImage',
                'tertiaryImage',
                'quaternaryImage',
            ];
            foreach($additionalImages as $imageCode) {
                if (!is_null($structureElement->getDataChunk($imageCode)->originalName)) {
                    $structureElement->$imageCode = $structureElement->id . "_$imageCode";
                    $field = $imageCode . 'OriginalName';
                    $structureElement->$field = $structureElement->getDataChunk($imageCode)->originalName;
                }
            }
            $structureElement->persistElementData();
        }
        if ($controller->getApplicationName() != 'adminAjax') {
            $controller->redirect($structureElement->URL);
        }
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'image',
            'secondaryImage',
            'tertiaryImage',
            'quaternaryImage',
            'link',
            'linkText',
            'content',
            'title',
            'fixedId',
            'marker',
            'highlighted',
        ];
    }
}