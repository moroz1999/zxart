<?php

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
            $approvalRequired = false;

            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            $commentsList = $structureManager->getElementsChildren($this->getId(), 'content', "commentTarget");
            foreach ($commentsList as $commentElement) {
                if (!$approvalRequired || $commentElement->approved) {
                    $this->commentsList[] = $commentElement;
                }
            }
        }
        return $this->commentsList;
    }

    public function getCommentsAmount()
    {
        if ($this->commentsAmount !== null) {
            $commentsAmount = $this->commentsAmount;
        } else {
            $linksManager = $this->getService('linksManager');
            $commentsAmount = count($linksManager->getElementsLinks($this->id, 'structure', 'parent', false));
        }

        return $commentsAmount;
    }

    public function getCommentForm()
    {
        /**
         * @var structureManager $structureManager
         */
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