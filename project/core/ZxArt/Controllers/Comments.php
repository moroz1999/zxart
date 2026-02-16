<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use ErrorLog;
use Symfony\Component\ObjectMapper\ObjectMapper;
use Throwable;
use ZxArt\Comments\CommentRestDto;
use ZxArt\Comments\CommentsListRestDto;
use ZxArt\Comments\CommentsService;
use ZxArt\Comments\Exception\CommentsException;

class Comments extends controllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        private readonly ObjectMapper $objectMapper,
        private readonly CommentsService $commentsService,
    ) {
        parent::__construct($controller);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        $action = $this->getParameter('action');
        if (!$action) {
            $this->handleGet();
        } elseif ($action === 'list') {
            $this->handleList();
        } elseif ($action === 'add') {
            $this->handleAdd();
        } elseif ($action === 'update') {
            $this->handleUpdate();
        } elseif ($action === 'delete') {
            $this->handleDelete();
        } elseif ($action === 'latest') {
            $this->handleLatest();
        } else {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Unknown action');
        }
        $this->renderer->display();
    }

    protected function handleList(): void
    {
        $page = (int)$this->getParameter('page') ?: 1;

        try {
            $listDto = $this->commentsService->getAllCommentsPaginated($page);
            $restDto = $this->objectMapper->map($listDto, CommentsListRestDto::class);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleList',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    protected function handleGet(): void
    {
        $elementId = (int)$this->getParameter('id');
        if (!$elementId) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'No ID provided');
            return;
        }

        try {
            $internalTree = $this->commentsService->getCommentsTree($elementId);
            $restTree = array_map(fn($dto) => $this->objectMapper->map($dto, CommentRestDto::class), $internalTree);

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restTree);
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $e->getMessage());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    protected function handleAdd(): void
    {
        $targetId = (int)$this->getParameter('id');
        $content = $this->getParameter('content');

        if (!$targetId || !$content) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing parameters');
            return;
        }

        try {
            $commentDto = $this->commentsService->addComment($targetId, $content);
            $restDto = $this->objectMapper->map($commentDto, CommentRestDto::class);
            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleAdd',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $e->getMessage());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleAdd',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Failed to add comment');
        }
    }

    protected function handleUpdate(): void
    {
        $commentId = (int)$this->getParameter('id');
        $content = $this->getParameter('content');

        if (!$commentId || !$content) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing parameters');
            return;
        }

        try {
            $commentDto = $this->commentsService->updateComment($commentId, $content);
            $restDto = $this->objectMapper->map($commentDto, CommentRestDto::class);
            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restDto);
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleUpdate',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $e->getMessage());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleUpdate',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Failed to update comment');
        }
    }

    protected function handleDelete(): void
    {
        $commentId = (int)$this->getParameter('id');
        if (!$commentId) {
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Missing ID');
            return;
        }

        try {
            $this->commentsService->deleteComment($commentId);
            $this->renderer->assign('responseStatus', 'success');
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleDelete',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', $e->getMessage());
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleDelete',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Failed to delete comment');
        }
    }

    protected function handleLatest(): void
    {
        $limit = (int)$this->getParameter('limit') ?: 10;

        try {
            $comments = $this->commentsService->getLatestComments($limit);
            $restComments = array_map(
                fn($dto) => $this->objectMapper->map($dto, CommentRestDto::class),
                $comments
            );

            $this->renderer->assign('responseStatus', 'success');
            $this->renderer->assign('responseData', $restComments);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleLatest',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->renderer->assign('responseStatus', 'error');
            $this->renderer->assign('errorMessage', 'Internal server error');
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
