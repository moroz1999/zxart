<?php

class receiveArticle extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param articleElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            if ($structureElement->getDataChunk('image')->originalName !== null) {
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk('image')->originalName;
            }
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();

            $structureElement->persistDisplayMenusLinks();

            $controller->redirect($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'hideTitle',
            'content',
            'displayMenus',
            'image',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}