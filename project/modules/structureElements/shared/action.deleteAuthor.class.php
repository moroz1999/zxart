<?php

class deleteAuthorShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($authorId = $controller->getParameter('authorId')) {
            /**
             * @var AuthorsManager $authorsManager
             */
            $authorsManager = $this->getService('AuthorsManager');
            $authorsManager->deleteAuthorship($structureElement->id, $authorId, 'prod');
            $authorsManager->deleteAuthorship($structureElement->id, $authorId, 'release');
            $authorsManager->deleteAuthorship($structureElement->id, $authorId, 'group');
        }
        $structureElement->executeAction('showPublicForm');
    }
}
