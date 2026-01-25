<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;
use Symfony\Component\ObjectMapper\ObjectMapper;
use ZxArt\Comments\CommentRestDto;
use ZxArt\Comments\CommentsService;

class Comments extends controllerApplication
{
    public $rendererName = 'json';
    protected ObjectMapper $objectMapper;

    public function initialize(): void
    {
        $this->createRenderer();
        $this->objectMapper = new ObjectMapper();
    }

    public function execute($controller): void
    {
        $elementId = (int)$this->getParameter('id');
        if ($elementId) {
            $configManager = $this->getService('ConfigManager');
            $structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => $controller->baseURL,
                    'rootMarker' => $configManager->get('main.rootMarkerPublic'),
                ],
                true
            );
            $languagesManager = $this->getService('LanguagesManager');
            $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

            $commentsService = $this->getService(CommentsService::class, [
                'structureManager' => $structureManager,
            ]);

            $internalTree = $commentsService->getCommentsTree($elementId);
            $restTree = array_map(fn($dto) => $this->objectMapper->map($dto, CommentRestDto::class), $internalTree);
            
            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restTree);
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'No ID provided');
        }
        $this->renderer->display();
    }
}
