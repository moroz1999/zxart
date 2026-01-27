<?php

use App\Users\CurrentUser;

class publicAddGroupAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param groupAliasElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService(CurrentUser::class);

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $structureElement->persistAuthorship('group');

            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getPersistedId(),
                'groupAlias',
                'showPublicForm',
                'allow'
            );
            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getPersistedId(),
                'groupAlias',
                'publicReceive',
                'allow'
            );
            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getPersistedId(),
                'groupAlias',
                'publicDelete',
                'allow'
            );
            $user->refreshPrivileges();

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
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


