<?php

use App\Users\CurrentUserService;
use ZxArt\Groups\Services\GroupAliasCreator;
use ZxArt\Groups\Services\GroupAliasFieldsDtoFactory;
use ZxArt\Shared\EntityType;

class publicAddGroupAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupAliasElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $fields = $this->getService(GroupAliasFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(GroupAliasCreator::class)->completeCreation($structureElement, $fields);
            $structureElement->persistAuthorship(EntityType::Group);
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
            'groupId',
            'addAuthor',
            'addAuthorStartDate',
            'addAuthorEndDate',
            'addAuthorRole',
            'importOrigins',
        ];
    }
}





