<?php

class receiveAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorElement $structureElement
     * @return mixed|void
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
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }
            if ($structureElement->joinAsAlias) {
                /**
                 * @var AuthorsManager $authorsManager
                 */
                $authorsManager = $this->getService('AuthorsManager');
                $authorsManager->joinAuthorAsAlias($structureElement->id, $structureElement->joinAsAlias);
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();

            $structureElement->recalculate();
            $structureElement->reconvertMusic();

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
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

    public function setValidators(&$validators)
    {
    }
}


