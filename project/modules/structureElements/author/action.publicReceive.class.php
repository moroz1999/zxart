<?php

use ZxArt\Authors\Services\AuthorFieldsDtoFactory;
use ZxArt\Authors\Services\AuthorFieldsWriter;
use ZxArt\Shared\EntityType;

class publicReceiveAuthor extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param authorElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->prepareActualData();
            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $fields = $this->getService(AuthorFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(AuthorFieldsWriter::class)->apply($structureElement, $fields);
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
            'realName',
            'country',
            'city',
            'wikiLink',
            'image',
            'denyVoting',
            'denyComments',
            'deny3a',
            'displayInMusic',
            'displayInGraphics',
            'chipType',
            'channelsType',
            'frequency',
            'intFrequency',
            'palette',
            'zxTunesId',
            'addGroupStartDate',
            'addGroupEndDate',
            'addGroupRole',
            'addProdRole',
            'importOrigins',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


