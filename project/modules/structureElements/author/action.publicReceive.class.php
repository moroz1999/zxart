<?php

use ZxArt\Shared\EntityType;

class publicReceiveAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            if ($structureElement->title == '') {
                $structureElement->title = $structureElement->realName;
            }
            $structureElement->structureName = $structureElement->title;

            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();
            $structureElement->persistMemberships(EntityType::Group);
            $structureElement->persistMemberships(EntityType::Prod);
            $structureElement->persistImportOrigins();

            $structureElement->recalculate();
            $structureElement->reconvertMusic();

            $this->respondFormSaved($controller, $structureElement);
            return;
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'realName',
            'country',
            'city',
            'wikiLink',
            'image',
            'denyVoting',
            'denyComments',
            'deny3a',
            'artCityId',
            'displayInMusic',
            'displayInGraphics',
            'chipType',
            'channelsType',
            'frequency',
            'intFrequency',
            'palette',
            'zxTunesId',
            'addGroupStartDate',
            'addGroupEndDate',
            'addGroupRole',
            'addProdRole',
            'importOrigins',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


