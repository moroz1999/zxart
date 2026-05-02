<?php

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
            if ($structureElement->title != '') {
                $structureElement->structureName = $structureElement->title;
            }

            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->file = (string)$structureElement->getPersistedId();
                $structureElement->fileName = $structureElement->getDataChunk("file")->originalName;

                $structureElement->parsed = 0;
            }

            $structureElement->persistElementData();

            $structureElement->persistAuthorship(EntityType::Release);

            $structureElement->executeAction('receiveFiles');
            $structureElement->updateFileStructure();
            $controller->redirect($structureElement->URL);
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
        ];
    }

    public function setValidators(&$validators): void
    {
        $validators['zxProd'][] = 'notEmpty';
    }
}

