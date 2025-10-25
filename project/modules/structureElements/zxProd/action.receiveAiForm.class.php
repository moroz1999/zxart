<?php


use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;

class receiveAiFormZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     *
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
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
