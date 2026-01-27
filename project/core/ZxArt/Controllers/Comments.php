<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use Symfony\Component\ObjectMapper\ObjectMapper;
use ZxArt\Comments\CommentRestDto;
use ZxArt\Comments\CommentsService;

class Comments extends controllerApplication
{
    public $rendererName = 'json';
    protected ObjectMapper $objectMapper;
    protected CommentsService $commentsService;

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
        $this->objectMapper = new ObjectMapper();

        $configManager = $this->getService('ConfigManager');
        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => controller::getInstance()->baseURL,
                'rootMarker' => $configManager->get('main.rootMarkerPublic'),
            ],
            true
        );
        $languagesManager = $this->getService('LanguagesManager');
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

        $this->commentsService = $this->getService(CommentsService::class);
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action');
        if (!$action) {
            $this->handleGet();
        } elseif ($action === 'add') {
            $this->handleAdd();
        } elseif ($action === 'update') {
            $this->handleUpdate();
        } elseif ($action === 'delete') {
            $this->handleDelete();
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Unknown action');
        }
        $this->renderer->display();
    }

    protected function handleGet(): void
    {
        $elementId = (int)$this->getParameter('id');
        if ($elementId) {
            $internalTree = $this->commentsService->getCommentsTree($elementId);
            $restTree = array_map(fn($dto) => $this->objectMapper->map($dto, CommentRestDto::class), $internalTree);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restTree);
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'No ID provided');
        }
    }

    protected function handleAdd(): void
    {
        $targetId = (int)$this->getParameter('id');
        $content = $this->getParameter('content');
        $author = $this->getParameter('author') ?: null;

        if ($targetId && $content) {
            $commentDto = $this->commentsService->addComment($targetId, $content, $author);
            if ($commentDto) {
                $restDto = $this->objectMapper->map($commentDto, CommentRestDto::class);
                $this->renderer->assign('responseStatus', 'success');
                $this->renderer->assign('responseData', $restDto);
            } else {
                $this->renderer->assign('responseStatus', 'error');
                $this->renderer->assign('errorMessage', 'Failed to add comment');
            }
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing parameters');
        }
    }

    protected function handleUpdate(): void
    {
        $commentId = (int)$this->getParameter('id');
        $content = $this->getParameter('content');

        if ($commentId && $content) {
            $commentDto = $this->commentsService->updateComment($commentId, $content);
            if ($commentDto) {
                $restDto = $this->objectMapper->map($commentDto, CommentRestDto::class);
                $this->renderer->assign('responseStatus', 'success');
                $this->renderer->assign('responseData', $restDto);
            } else {
                $this->renderer->assign('responseStatus', 'error');
                $this->renderer->assign('errorMessage', 'Failed to update comment');
            }
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing parameters');
        }
    }

    protected function handleDelete(): void
    {
        $commentId = (int)$this->getParameter('id');
        if ($commentId) {
            if ($this->commentsService->deleteComment($commentId)) {
                $this->renderer->assign('responseStatus', 'success');
            } else {
                $this->renderer->assign('responseStatus', 'error');
                $this->renderer->assign('errorMessage', 'Failed to delete comment');
            }
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing ID');
        }
    }
}
