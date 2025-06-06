<?php

use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;

class publicReceiveZxProd extends structureElementAction
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
        if ($this->validated) {
            $structureElement->structureName = $structureElement->title;
            $structureElement->dateAdded = $structureElement->dateCreated;

            $oldCategoriesIds = $structureElement->getConnectedCategoriesIds();

            $structureElement->renewPartyLink();
            $structureElement->updateTagsInfo();
            $structureElement->updateYear();

            $structureElement->persistElementData();
            $structureElement->checkAndPersistCategories();
            $structureElement->persistAuthorship('prod');

            /**
             * @var QueueService $queueService
             */
            $queueService = $this->getService('QueueService');
            $queueService->updateStatus($structureElement->getId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);
            if (array_diff($oldCategoriesIds, $structureElement->categories) || array_diff($structureElement->categories, $oldCategoriesIds)) {
                $queueService->updateStatus($structureElement->getId(), QueueType::AI_SEO, QueueStatus::STATUS_TODO);
                $queueService->updateStatus($structureElement->getId(), QueueType::AI_INTRO, QueueStatus::STATUS_TODO);
            }

            $structureElement->executeAction('receiveFiles');

            $controller->redirect($structureElement->URL);
        }
        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields): void
    {
        $expectedFields = [
            'title',
            'altTitle',
            'party',
            'partyplace',
            'compo',
            'year',
            'youtubeId',
            'publishers',
            'groups',
            'description',
            'tagsText',
            'denyVoting',
            'denyComments',
            'addAuthor',
            'addAuthorRole',
            'legalStatus',
            'language',
            'categories',
            'compilationItems',
            'seriesProds',
            'externalLink',
        ];
    }

    public function setValidators(&$validators): void
    {
    }
}


