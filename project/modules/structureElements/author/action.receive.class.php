<?php

use ZxArt\Authors\Services\AuthorsService;

class receiveAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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
            if ($structureElement->joinAsAlias) {
                $authorsManager = $this->getService(AuthorsService::class);
                $authorsManager->joinAuthorAsAlias($structureElement->getId(), $structureElement->joinAsAlias);
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();

            $structureElement->recalculate();
            $structureElement->reconvertMusic();

            $controller->redirect($structureElement->URL);
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
            'artCityId',
            'displayInMusic',
            'displayInGraphics',
            'chipType',
            'channelsType',
            'frequency',
            'intFrequency',
            'palette',
            'joinAsAlias',
            'zxTunesId',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


