<?php

use App\Users\CurrentUserService;

class receivePlaylist extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();

            $structureElement->prepareActualData();

            $structureElement->userId = $user->id;
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }

            $structureElement->persistElementData();

            $linksManager = $this->getService(linksManager::class);
            $privilegesManager = $this->getService(privilegesManager::class);

            if ($firstParentElement = $structureManager->getElementsFirstParent($structureElement->getId())) {
                $linksManager->unLinkElements($firstParentElement->getId(), $structureElement->getId(), 'structure');
            }

            $linksManager->linkElements($user->id, $structureElement->getId(), 'structure');

            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'playlist', 'delete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'playlist', 'receive', 'allow');
            if ($registeredUsersGroupId = $structureManager->getElementIdByMarker('userGroup-authorized')) {
                $privilegesManager->setPrivilege(
                    $registeredUsersGroupId,
                    $structureElement->getId(),
                    'playlist',
                    'delete',
                    0
                );
                $privilegesManager->setPrivilege(
                    $registeredUsersGroupId,
                    $structureElement->getId(),
                    'playlist',
                    'receive',
                    0
                );
            }

            $renderer = $this->getService(renderer::class);
            if ($renderer instanceof rendererPluginAppendInterface) {
                $renderer->appendResponseData('playlist', $structureElement->getElementData());
            }
            if ($controller->getApplicationName() != 'ajax') {
                $controller->restart($structureElement->URL);
            }
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = ['title'];
    }

    public function setValidators(&$validators): void
    {
    }
}





