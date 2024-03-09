<?php

class receivePlaylist extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if ($user = $this->getService('user')) {
                $structureElement->prepareActualData();

                $structureElement->userId = $user->id;
                if ($structureElement->structureName == '') {
                    $structureElement->structureName = $structureElement->title;
                }

                $structureElement->persistElementData();

                $linksManager = $this->getService('linksManager');
                $privilegesManager = $this->getService('privilegesManager');

                if ($firstParentElement = $structureManager->getElementsFirstParent($structureElement->id)) {
                    $linksManager->unLinkElements($firstParentElement->id, $structureElement->id, 'structure');
                }

                $linksManager->linkElements($user->id, $structureElement->id, 'structure');

                $privilegesManager->setPrivilege($user->id, $structureElement->id, 'playlist', 'delete', 'allow');
                $privilegesManager->setPrivilege($user->id, $structureElement->id, 'playlist', 'receive', 'allow');
                if ($registeredUsersGroupId = $structureManager->getElementIdByMarker('userGroup-authorized')) {
                    $privilegesManager->setPrivilege(
                        $registeredUsersGroupId,
                        $structureElement->id,
                        'playlist',
                        'delete',
                        0
                    );
                    $privilegesManager->setPrivilege(
                        $registeredUsersGroupId,
                        $structureElement->id,
                        'playlist',
                        'receive',
                        0
                    );
                }
            }
            $renderer = $this->getService('renderer');
            if ($renderer instanceof rendererPluginAppendInterface) {
                $renderer->appendResponseData('playlist', $structureElement->getElementData());
            }
            if ($controller->getApplicationName() != 'ajax') {
                $controller->restart($structureElement->URL);
            }
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['title'];
    }

    public function setValidators(&$validators)
    {
    }
}


