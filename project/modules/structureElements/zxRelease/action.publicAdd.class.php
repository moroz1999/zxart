<?php

class publicAddZxRelease extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxReleaseElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if (!is_null($structureElement->getDataChunk("file")->originalName)) {
                $structureElement->file = $structureElement->getId();
                $structureElement->fileName = $structureElement->getDataChunk("file")->originalName;
            }
            if (!$structureElement->title) {
                $info = pathinfo($structureElement->fileName);
                $structureElement->title = str_replace('_', ' ', ucfirst(ucfirst($info['filename'])));
            }
            if (!$structureElement->structureName) {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->dateAdded = time();
            $structureElement->userId = $this->getService('user')->id;
            $structureElement->persistElementData();

            $structureElement->persistAuthorship('release');

            $structureElement->executeAction('receiveFiles');

            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService('user');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'zxRelease', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'zxRelease', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'zxRelease', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'zxRelease', 'deleteFile', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'zxRelease', 'clone', 'allow');
            $user->refreshPrivileges();

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


