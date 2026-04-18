<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Shared\EntityType;

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
            $authorshipRepository->deleteAuthorship($structureElement->getId(), $authorId, EntityType::Prod);
            $authorshipRepository->deleteAuthorship($structureElement->getId(), $authorId, EntityType::Release);
            $authorshipRepository->deleteAuthorship($structureElement->getId(), $authorId, EntityType::Group);
        }
        $structureElement->executeAction('showPublicForm');
    }
}
