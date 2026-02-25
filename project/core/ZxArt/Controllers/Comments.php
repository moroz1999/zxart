<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
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
            $this->assignError('Unknown action', 400);
        }
        $this->renderer->display();
    }

    protected function handleList(): void
    {
        $page = (int)$this->getParameter('page') ?: 1;

        try {
            $listDto = $this->commentsService->getAllCommentsPaginated($page);
            $this->assignSuccess($this->objectMapper->map($listDto, CommentsListRestDto::class));
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleList',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Internal server error');
        }
    }

    protected function handleGet(): void
    {
        $elementId = (int)$this->getParameter('id');
        if (!$elementId) {
            $this->assignError('No ID provided', 400);
            return;
        }

        try {
            $internalTree = $this->commentsService->getCommentsTree($elementId);
            $restTree = array_map(fn($dto) => $this->objectMapper->map($dto, CommentRestDto::class), $internalTree);
            $this->assignSuccess($restTree);
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError($e->getMessage(), 400);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleGet',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Internal server error');
        }
    }

    protected function handleAdd(): void
    {
        $targetId = (int)$this->getParameter('id');
        $content = $this->getParameter('content');

        if (!$targetId || !$content) {
            $this->assignError('Missing parameters', 400);
            return;
        }

        try {
            $commentDto = $this->commentsService->addComment($targetId, $content);
            $this->assignSuccess($this->objectMapper->map($commentDto, CommentRestDto::class));
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleAdd',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError($e->getMessage(), 400);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleAdd',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Failed to add comment');
        }
    }

    protected function handleUpdate(): void
    {
        $commentId = (int)$this->getParameter('id');
        $content = $this->getParameter('content');

        if (!$commentId || !$content) {
            $this->assignError('Missing parameters', 400);
            return;
        }

        try {
            $commentDto = $this->commentsService->updateComment($commentId, $content);
            $this->assignSuccess($this->objectMapper->map($commentDto, CommentRestDto::class));
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleUpdate',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError($e->getMessage(), 400);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleUpdate',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Failed to update comment');
        }
    }

    protected function handleDelete(): void
    {
        $commentId = (int)$this->getParameter('id');
        if (!$commentId) {
            $this->assignError('Missing ID', 400);
            return;
        }

        try {
            $this->commentsService->deleteComment($commentId);
            $this->assignSuccess(null);
        } catch (CommentsException $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleDelete',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError($e->getMessage(), 400);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleDelete',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Failed to delete comment');
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
            $this->assignSuccess($restComments);
        } catch (Throwable $e) {
            ErrorLog::getInstance()->logMessage(
                'Comments::handleLatest',
                $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            $this->assignError('Internal server error');
        }
    }

    private function assignSuccess(mixed $data): void
    {
        $this->renderer->assign('body', $data);
    }

    private function assignError(string $message, int $statusCode = 500): void
    {
        CmsHttpResponse::getInstance()->setStatusCode((string)$statusCode);
        $this->renderer->assign('body', ['errorMessage' => $message]);
    }

    public function getUrlName()
    {
        return '';
    }
}
