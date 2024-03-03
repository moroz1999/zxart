<?php

class publicReceiveGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            $structureElement->persistElementData();
            $structureElement->checkParentLetter();
            $structureElement->persistSubGroupConnections();
            $structureElement->persistAuthorship('group');
            $structureElement->recalculate();

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'country',
            'city',
            'image',
            'wikiLink',
            'website',
            'abbreviation',
            'type',
            'startDate',
            'endDate',
            'slogan',
            'type',
            'addAuthor',
            'addAuthorStartDate',
            'addAuthorEndDate',
            'addAuthorRole',
            'subGroupsSelector',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


