<?php


use ZxArt\Press\Repositories\PressArticleRepository;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;

class receiveAiFormPressArticle extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param pressArticleElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            /**
             * @var QueueService $queueService
             */
            $queueService = $this->getService('QueueService');
            if ($structureElement->aiRestartFix) {
                $this->restoreOriginalContent($structureElement);
                $structureManager->clearElementCache($structureElement->getId());
                $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_PRESS_FIX, QueueStatus::STATUS_TODO);
            }

            if ($structureElement->aiRestartTranslate) {
                $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_PRESS_TRANSLATE, QueueStatus::STATUS_TODO);
            }

            if ($structureElement->aiRestartParse) {
                $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_PRESS_PARSE, QueueStatus::STATUS_TODO);
            }
            if ($structureElement->aiRestartSeo) {
                $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_PRESS_SEO, QueueStatus::STATUS_TODO);
            }

            $controller->redirect($structureElement->getUrl());
        }

        $structureElement->setViewName('form');
    }

    public function restoreOriginalContent($structureElement)
    {
        $pressArticleRepository = $this->getService(PressArticleRepository::class);
        $pressArticleRepository->restoreContent($structureElement->getId());
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'aiRestartFix',
            'aiRestartTranslate',
            'aiRestartParse',
            'aiRestartSeo',
        ];
    }
}


