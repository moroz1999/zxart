<?php

declare(strict_types=1);

namespace ZxArt\Social;

use Exception;
use Psr\Log\LoggerInterface;
use structureElement;
use structureManager;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Telegram\PostDto;
use ZxArt\Telegram\PostService;

readonly class SocialPostsService
{
    private const int PROCESS_LIMIT_SECONDS = 300;
    private const int ITERATION_SLEEP_SECONDS = 1;

    public function __construct(
        private PostService           $postService,
        private QueueService          $queueService,
        private structureManager      $structureManager,
        private SocialPostTransformer $socialPostTransformer,
        private LoggerInterface       $logger,
    )
    {
    }

    public function processQueue(): void
    {
        $totalExecution = 0.0;

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

            $startTime = microtime(true);
            $this->sendPostForElement($elementId, $element);
            sleep(self::ITERATION_SLEEP_SECONDS);
            $endTime = microtime(true);
            $totalExecution += ($endTime - $startTime);
        }
    }

    private function markInProgressAndGetElement(int $elementId): ?structureElement
    {
        $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_INPROGRESS);

        $element = $this->structureManager->getElementById($elementId);
        if ($element === false || !($element instanceof structureElement)) {
            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
            $this->logger->error("Social post element not found: $elementId");
            return null;
        }

        return $element;
    }

    private function sendPostForElement(int $elementId, structureElement $element): void
    {
        $postDto = null;

        try {
            $postDto = $this->socialPostTransformer->transform($element);
            if ($postDto === null) {
                $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SKIP);
                $this->logger->info("Social post skipped for element $element->structureType $elementId: $element->title");
                return;
            }

            $this->postService->sendPost($postDto);
            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SUCCESS);
            $this->logger->info("Social post sent successfully for element $element->structureType $elementId: $element->title");
        } catch (Exception $exception) {
            $postData = $this->preparePostDataForLog($postDto);
            $this->logger->error("Failed to send social post for element $element->structureType $elementId. Error: " . $exception->getMessage() . ". Data sent: " . $postData);
            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
        }
    }

    private function preparePostDataForLog(?PostDto $postDto): string
    {
        if ($postDto === null) {
            return 'null';
        }

        return json_encode([
            'title' => $postDto->title,
            'link' => $postDto->link,
            'image' => $postDto->image,
            'audio' => $postDto->audio,
            'description' => $postDto->description,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
