<?php


use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;

class receiveAiFormZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param zxProdElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $queueService = $this->getService(QueueService::class);
        if ($structureElement->aiRestartSeo) {
            $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_SEO, QueueStatus::STATUS_TODO);
        }
        if ($structureElement->aiRestartIntro) {
            $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_INTRO, QueueStatus::STATUS_TODO);
        }
        if ($structureElement->aiRestartCategories) {
            $queueService->updateStatus($structureElement->getPersistedId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_TODO);
        }

        $controller->redirect($structureElement->getUrl());
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'aiRestartSeo',
            'aiRestartIntro',
            'aiRestartCategories',
        ];
    }
}
