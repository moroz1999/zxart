<?php

class receiveGroup extends structureElementAction
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
                $structureElement->image = $structureElement->id;
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();
            $structureElement->persistSubGroupConnections();
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            $authorsManager->checkAuthorship(
                $structureElement->id,
                $structureElement->getValue('addAuthor'),
                'group',
                $structureElement->getValue('addAuthorRole'),
                $structureElement->getValue('addAuthorStartDate'),
                $structureElement->getValue('addAuthorEndDate')
            );

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


