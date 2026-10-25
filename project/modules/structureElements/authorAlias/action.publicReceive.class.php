<?php

use ZxArt\Authors\Services\AuthorAliasFieldsDtoFactory;
use ZxArt\Authors\Services\AuthorAliasFieldsWriter;
use ZxArt\Shared\EntityType;

class publicReceiveAuthorAlias extends structureElementAction
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
            $this->getService(AuthorAliasFieldsWriter::class)->apply($structureElement, $fields);
            $structureElement->persistMemberships(EntityType::Group);
            $structureElement->persistMemberships(EntityType::Prod);
            $structureElement->persistImportOrigins();

            $this->respondFormSaved($controller, $structureElement);
            return;
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
            'addGroupStartDate',
            'addGroupEndDate',
            'addGroupRole',
            'addProdRole',
            'importOrigins',
        ];
    }

}


