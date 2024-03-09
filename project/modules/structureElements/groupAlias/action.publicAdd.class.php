<?php

class publicAddGroupAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupAliasElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService('user');

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $structureElement->persistAuthorship('group');

            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getId(),
                'groupAlias',
                'showPublicForm',
                'allow'
            );
            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getId(),
                'groupAlias',
                'publicReceive',
                'allow'
            );
            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getId(),
                'groupAlias',
                'publicDelete',
                'allow'
            );
            $user->refreshPrivileges();

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'startDate',
            'endDate',
            'groupId',
            'addAuthor',
            'addAuthorStartDate',
            'addAuthorEndDate',
            'addAuthorRole',
        ];
    }
}


