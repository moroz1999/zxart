<?php

use App\Users\CurrentUserService;
use ZxArt\Shared\EntityType;

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
            $privilegesManager = $this->getService(privilegesManager::class);
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $structureElement->persistAuthorship(EntityType::Group->value);

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





