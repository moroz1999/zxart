<?php

use ZxArt\Shared\EntityType;

class publicReceiveAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();
            $structureElement->checkParentLetter();
            $structureElement->persistMemberships(EntityType::Group);
            $structureElement->persistMemberships(EntityType::Prod);

            $this->respondFormSaved($controller, $structureElement);
            return;
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'startDate',
            'endDate',
            'authorId',
            'displayInMusic',
            'displayInGraphics',
            'addGroupStartDate',
            'addGroupEndDate',
            'addGroupRole',
            'addProdRole',
        ];
    }

}


