<?php

class publicAddGroup extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;

            if (!is_null($structureElement->getDataChunk("image")->originalName)) {
                $structureElement->image = $structureElement->getPersistedId();
                $structureElement->originalName = $structureElement->getDataChunk("image")->originalName;
            }

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();
            $structureElement->persistSubGroupConnections();
            $structureElement->persistAuthorship('group');
            $structureElement->recalculate();

            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService(user::class);
            $privilegesManager->setPrivilege($user->id, $structureElement->getPersistedId(), 'group', 'showPublicForm', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getPersistedId(), 'group', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getPersistedId(), 'group', 'publicDelete', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getPersistedId(), 'group', 'deleteFile', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getPersistedId(), 'group', 'deleteAuthor', 'allow');
            $user->refreshPrivileges();

            $controller->redirect($structureElement->URL);
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
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


