<?php

class joinAuthor extends structureElementAction
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
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');

            if ($structureElement->joinAsAlias) {
                $authorsManager->joinAuthorAsAlias($structureElement->id, $structureElement->joinAsAlias);
            }
            if ($structureElement->joinAndDelete) {
                $authorsManager->joinDeleteAuthor($structureElement->id, $structureElement->joinAndDelete);
            }
            $structureElement->recalculate();

            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'joinAsAlias',
            'joinAndDelete',
        ];
    }
}


