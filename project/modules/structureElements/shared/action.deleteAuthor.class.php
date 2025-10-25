<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;

class deleteAuthorShared extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($authorId = $controller->getParameter('authorId')) {
            $authorshipRepository = $this->getService(AuthorshipRepository::class);
            $authorshipRepository->deleteAuthorship($structureElement->getId(), $authorId, 'prod');
            $authorshipRepository->deleteAuthorship($structureElement->getId(), $authorId, 'release');
            $authorshipRepository->deleteAuthorship($structureElement->getId(), $authorId, 'group');
        }
        $structureElement->executeAction('showPublicForm');
    }
}
