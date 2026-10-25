<?php

use App\Users\CurrentUserService;
use ZxArt\Groups\Services\GroupCreator;
use ZxArt\Groups\Services\GroupFieldsDtoFactory;
use ZxArt\Shared\EntityType;

class publicAddGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getPersistedId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $fields = $this->getService(GroupFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(GroupCreator::class)->completeCreation($structureElement, $fields);
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
            'type',
            'addAuthor',
            'addAuthorStartDate',
            'addAuthorEndDate',
            'addAuthorRole',
            'subGroupsSelector',
            'importOrigins',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}




