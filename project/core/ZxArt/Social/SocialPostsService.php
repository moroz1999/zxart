<?php

declare(strict_types=1);

namespace ZxArt\Social;

use Psr\Log\LoggerInterface;
use structureElement;
use structureManager;
use Throwable;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Telegram\PostDto;
use ZxArt\Telegram\PostService;
use zxProdElement;
use zxReleaseElement;

readonly class SocialPostsService
{
    private const int PROCESS_LIMIT_SECONDS = 300;
    private const int ITERATION_SLEEP_SECONDS = 1;
    private const int LOOKAHEAD_LIMIT = 100;

    public function __construct(
        private PostService           $postService,
        private QueueService          $queueService,
        private structureManager      $structureManager,
        private SocialPostTransformer $socialPostTransformer,
        private SocialPostFilter      $socialPostFilter,
        private LoggerInterface       $logger,
    ) {
    }

    public function processQueue(): void
    {
        $totalExecution = 0.0;
        $postedProdIds = [];

        $this->logger->info('Social posts processing started');

        while ($totalExecution <= (float)self::PROCESS_LIMIT_SECONDS) {
            $elementId = $this->queueService->getNextElementId(QueueType::SOCIAL_POST);
            if ($elementId === null) {
                break;
            }

            $element = $this->markInProgressAndGetElement($elementId);
            if ($element === null) {
                continue;
            }

            if ($this->socialPostFilter->shouldSkip($element)) {
                $this->markSkipped($elementId, $element);
                continue;
            }

            if ($element instanceof zxReleaseElement && $this->releaseShouldBeSkipped($element, $postedProdIds)) {
                $this->markSkipped($elementId, $element);
                continue;
            }

            $startTime = microtime(true);
            $posted = $this->sendPostForElement($elementId, $element);
            if ($posted && $element instanceof zxProdElement) {
                $postedProdIds[] = (int)$element->id;
            }
            sleep(self::ITERATION_SLEEP_SECONDS);
            $totalExecution += (microtime(true) - $startTime);
        }
    }

    /**
     * @param int[] $postedProdIds
     */
    private function releaseShouldBeSkipped(zxReleaseElement $element, array $postedProdIds): bool
    {
        $prod = $element->getProd();
        if ($prod === null) {
            return false;
        }
        $prodId = (int)$prod->id;
        if (in_array($prodId, $postedProdIds, true)) {
            return true;
        }
        $upcomingIds = $this->queueService->getUpcomingElementIds(QueueType::SOCIAL_POST, self::LOOKAHEAD_LIMIT);
        return in_array($prodId, $upcomingIds, true);
    }

    private function markInProgressAndGetElement(int $elementId): ?structureElement
    {
        $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_INPROGRESS);

        $element = $this->structureManager->getElementById($elementId);
        if (!($element instanceof structureElement)) {
            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
            $this->logger->error("Social post element not found: $elementId");
            return null;
        }

        return $element;
    }

    private function markSkipped(int $elementId, structureElement $element): void
    {
        $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SKIP);
        $type = $element->structureType;
        $title = $element->title;
        $this->logger->info("Social post skipped: $type $elementId: $title");
    }

    private function sendPostForElement(int $elementId, structureElement $element): bool
    {
        $postDto = null;

        try {
            $postDto = $this->socialPostTransformer->transform($element);
            if ($postDto === null) {
                $this->markSkipped($elementId, $element);
                return false;
            }

            $this->postService->sendPost($postDto);
            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SUCCESS);
            $type = $element->structureType;
            $title = $element->title;
            $this->logger->info("Social post sent: $type $elementId: $title");
            return true;
        } catch (Throwable $exception) {
            $postData = $this->preparePostDataForLog($postDto);
            $type = $element->structureType;
            $this->logger->error("Failed to send social post for $type $elementId. Error: {$exception->getMessage()}. Data: $postData");
            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
            return false;
        }
    }

    private function preparePostDataForLog(?PostDto $postDto): string
    {
        if ($postDto === null) {
            return 'null';
        }

        return (string)json_encode([
            'title' => $postDto->title,
            'link' => $postDto->link,
            'image' => $postDto->image,
            'audio' => $postDto->audio,
            'description' => $postDto->description,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
