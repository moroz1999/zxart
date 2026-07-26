<?php

use ZxArt\Authors\Services\AuthorsService;

class joinAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $authorsManager = $this->getService(AuthorsService::class);

            if ($structureElement->joinAndDelete) {
                $authorsManager->joinDeleteAuthor($structureElement->getId(), $structureElement->joinAndDelete);
            }
            $this->respondFormSaved($controller, $structureElement); return;
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'joinAndDelete',
        ];
    }
}


