<?php

class publicAddAuthorAlias extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param authorAliasElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            $privilegesManager = $this->getService('privilegesManager');
            $user = $this->getService('user');

            $structureElement->prepareActualData();

            $structureElement->structureName = $structureElement->title;

            $structureElement->persistElementData();
            $structureElement->checkParentLetter();

            $privilegesManager->setPrivilege(
                $user->id,
                $structureElement->id,
                'authorAlias',
                'showPublicForm',
                'allow'
            );
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'authorAlias', 'publicReceive', 'allow');
            $privilegesManager->setPrivilege($user->id, $structureElement->id, 'authorAlias', 'publicDelete', 'allow');
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
            'authorId',
            'displayInMusic',
            'displayInGraphics',
        ];
    }
}


