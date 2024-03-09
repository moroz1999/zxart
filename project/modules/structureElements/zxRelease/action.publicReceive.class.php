<?php

class publicReceiveZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if ($structureElement->title != '') {
                $structureElement->structureName = $structureElement->title;
            }

            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->file = $structureElement->getId();
                $structureElement->fileName = $structureElement->getDataChunk("file")->originalName;

                $structureElement->parsed = 0;
            }

            $structureElement->persistElementData();

            $structureElement->persistAuthorship('release');

            $structureElement->executeAction('receiveFiles');
            $structureElement->updateFileStructure();
            $controller->redirect($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
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

    public function setValidators(&$validators)
    {
        $validators['zxProd'][] = 'notEmpty';
    }
}


