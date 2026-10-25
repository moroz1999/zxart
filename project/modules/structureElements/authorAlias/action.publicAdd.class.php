<?php

use App\Users\CurrentUserService;
use ZxArt\Authors\Services\AuthorAliasCreator;
use ZxArt\Authors\Services\AuthorAliasFieldsDtoFactory;

class publicAddAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $fields = $this->getService(AuthorAliasFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(AuthorAliasCreator::class)->completeCreation($structureElement, $fields);
            $structureElement->persistImportOrigins();
            $this->getService(CurrentUserService::class)->getCurrentUser()->refreshPrivileges();

            $this->respondFormSaved($controller, $structureElement); return;
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
            'importOrigins',
        ];
    }
}





