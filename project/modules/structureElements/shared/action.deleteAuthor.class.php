<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Shared\EntityType;

class deleteAuthorShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
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
