<?php

use App\Users\CurrentUserService;

class publicAddAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorAliasElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $privilegesManager = $this->getService('privilegesManager');
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();

            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();

            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->getId(),
                'authorAlias',
                'showPublicForm',
                'allow'
            );
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'authorAlias', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->getId(), 'authorAlias', 'publicDelete', 'allow');
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
            'authorId',
            'displayInMusic',
            'displayInGraphics',
        ];
    }
}





