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
            $authorshipRepository->deleteAuthorship($structureElement->id, $authorId, 'prod');
            $authorshipRepository->deleteAuthorship($structureElement->id, $authorId, 'release');
            $authorshipRepository->deleteAuthorship($structureElement->id, $authorId, 'group');
        }
        $structureElement->executeAction('showPublicForm');
    }
}
