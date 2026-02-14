<?php

use App\Users\CurrentUserService;

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
                $structureElement->file = (string)$structureElement->getPersistedId();
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
            $currentUserService = $this->getService(CurrentUserService::class);
            $structureElement->userId = $currentUserService->getCurrentUser()->id;
            $structureElement->persistElementData();

            $structureElement->persistAuthorship('release');

            $structureElement->executeAction('receiveFiles');

            $privilegesManager = $this->getService('privilegesManager');
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'zxRelease', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'zxRelease', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'zxRelease', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'zxRelease', 'deleteFile', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'zxRelease', 'clone', 'allow');
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





