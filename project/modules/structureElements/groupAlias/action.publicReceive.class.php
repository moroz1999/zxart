<?php

use ZxArt\Shared\EntityType;

class publicReceiveGroupAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->persistElementData();
            $structureElement->persistAuthorship(EntityType::Group);

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'startDate',
            'endDate',
            'groupId',
            'displayInMusic',
            'displayInGraphics',
            'addAuthor',
            'addAuthorStartDate',
            'addAuthorEndDate',
            'addAuthorRole',
        ];
    }

}

