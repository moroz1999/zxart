<?php

class publicReceiveZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->dateAdded = $structureElement->dateCreated;

            $structureElement->renewPartyLink();
            $structureElement->updateTagsInfo();
            $structureElement->updateYear();

            $structureElement->persistElementData();

            $structureElement->checkLinks('categories', 'zxProdCategory');
            $structureElement->persistAuthorship('prod');

            $structureElement->executeAction('receiveFiles');

            $controller->redirect($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'party',
            'partyplace',
            'compo',
            'year',
            'youtubeId',
            'publishers',
            'groups',
            'description',
            'tagsText',
            'denyVoting',
            'denyComments',
            'addAuthor',
            'addAuthorRole',
            'legalStatus',
            'language',
            'categories',
            'compilationProds',
            'externalLink',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}


