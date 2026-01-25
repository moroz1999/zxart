<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;
use ZxArt\Comments\CommentsService;

class Comments extends controllerApplication
{
    public $rendererName = 'json';
    protected CommentsService $commentsService;

    public function initialize(): void
    {
        $this->createRenderer();
        $this->commentsService = $this->getService(CommentsService::class);
    }

    public function execute($controller): void
    {
        $elementId = (int)$this->getParameter('id');
        if ($elementId) {
            $structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => $controller->baseURL,
                    'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
                ],
                true
            );
            $languagesManager = $this->getService('LanguagesManager');
            $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

            $this->commentsService->setStructureManager($structureManager);
            $tree = $this->commentsService->getCommentsTree($elementId);
            
            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $tree);
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'No ID provided');
        }
        $this->renderer->display();
    }
}
