<?php

use ZxArt\Comments\CommentsService;

/**
 * @this structureElement
 */
trait CommentsTrait
{
    protected $commentsList;
    protected $commentForm;
    protected $commentsConfig;

    public function getCommentFormActionURL()
    {
        return $this->getFormActionURL();
    }

    public function getCommentsList()
    {
        if ($this->commentsList === null) {
            $this->commentsList = [];

            $commentsService = $this->getService(CommentsService::class);
            $this->commentsList = $commentsService->getCommentsList((int)$this->id);
        }
        return $this->commentsList;
    }

    public function getCommentsAmount()
    {
        if ($this->commentsAmount !== null) {
            $commentsAmount = $this->commentsAmount;
        } else {
            $linksManager = $this->getService(linksManager::class);
            $commentsAmount = count($linksManager->getElementsLinks($this->id, 'structure', 'parent', false));
        }

        return $commentsAmount;
    }

    public function getCommentForm()
    {
        $structureManager = $this->getService('structureManager');

        if ($commentForm = $structureManager->createElement(
            'comment',
            'showForm',
            $this->id
        )){
            $commentForm->setViewName('form');
        }
        return $commentForm;
    }

    public function recalculateComments()
    {
    }

    public function areCommentsAllowed()
    {
        return $this->getCommentsConfig()->get($this->structureType . '.allowed');
    }

    public function areCommentsRegisteredOnly()
    {
        return $this->getCommentsConfig()->get($this->structureType . '.registeredOnly');
    }

    protected function getCommentsConfig()
    {
        if ($this->commentsConfig === null) {
            $this->commentsConfig = controller::getInstance()
                ->getConfigManager()->getConfig('comments');
        }
        return $this->commentsConfig;
    }
}