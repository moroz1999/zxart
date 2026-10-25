<?php

use ZxArt\Forms\StagedUploadsCleaner;
use ZxArt\Prods\Services\ProdFieldsDtoFactory;
use ZxArt\Prods\Services\ProdFieldsWriter;
use ZxArt\Shared\EntityType;

class publicReceiveZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->dateAdded = $structureElement->getCreatedTimestamp();

            $fields = $this->getService(ProdFieldsDtoFactory::class)->fromSubmittedForm(
                $structureElement,
                $this->expectedFields,
                (string)$structureElement->getValue('title'),
            );
            $this->getService(ProdFieldsWriter::class)->apply($structureElement, $fields);
            $structureElement->persistAuthorship(EntityType::Prod);
            $structureElement->persistImportOrigins();

            $structureElement->executeAction('receiveFiles');
            $this->getService(StagedUploadsCleaner::class)->clearElementUploads($structureElement);

            $this->respondFormSaved($controller, $structureElement); return;
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'altTitle',
            'party',
            'partyplace',
            'compo',
            'year',
            'youtubeId',
            'publishers',
            'groups',
            'htmlDescription',
            'htmlInstructions',
            'description',
            'instructions',
            'tags',
            'denyVoting',
            'denyComments',
            'addAuthor',
            'addAuthorRole',
            'legalStatus',
            'language',
            'hardwareRequired',
            'categories',
            'compilationItems',
            'seriesProds',
            'externalLink',
            'importOrigins',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}

