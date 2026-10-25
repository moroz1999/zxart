<?php

use ZxArt\Groups\Services\GroupFieldsDtoFactory;
use ZxArt\Groups\Services\GroupFieldsWriter;
use ZxArt\Shared\EntityType;

class publicReceiveGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param groupElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getPersistedId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $fields = $this->getService(GroupFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(GroupFieldsWriter::class)->apply($structureElement, $fields);
            $structureElement->persistAuthorship(EntityType::Group);
            $structureElement->persistImportOrigins();

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

