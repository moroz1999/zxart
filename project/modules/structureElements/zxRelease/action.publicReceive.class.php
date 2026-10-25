<?php

use ZxArt\Forms\StagedUploadsCleaner;
use ZxArt\Releases\Services\ReleaseFieldsDtoFactory;
use ZxArt\Releases\Services\ReleaseFieldsWriter;
use ZxArt\Shared\EntityType;

class publicReceiveZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxReleaseElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->file = (string)$structureElement->getPersistedId();
                $structureElement->fileName = $structureElement->getDataChunk("file")->originalName;

                $structureElement->parsed = 0;
            }

            $fields = $this->getService(ReleaseFieldsDtoFactory::class)
                ->fromSubmittedForm($structureElement, $this->expectedFields);
            $this->getService(ReleaseFieldsWriter::class)->apply($structureElement, $fields);

            $structureElement->persistAuthorship(EntityType::Release);
            $structureElement->persistImportOrigins();

            $structureElement->executeAction('receiveFiles');
            $this->getService(StagedUploadsCleaner::class)->clearElementUploads($structureElement);
            $structureElement->updateFileStructure();
            $this->respondFormSaved($controller, $structureElement); return;
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'year',
            'version',
            'description',
            'file',
            'denyVoting',
            'denyComments',
            'releaseType',
            'releaseFormat',
            'language',
            'hardwareRequired',
            'addAuthor',
            'addAuthorRole',
            'publishers',
            'zxProd',
            'importOrigins',
        ];
    }

    public function setValidators(&$validators): void
    {
        $validators['zxProd'][] = 'notEmpty';
    }
}

