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
use ZxArt\Telegram\PostService;

readonly class SocialPostsService
{
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
        $executionLimit = 60 * 5; // 5 minutes limit
        $totalExecution = 0.0;

        $this->logger->info('Social posts processing started');

        while ($totalExecution <= (float)$executionLimit) {
            $elementId = $this->queueService->getNextElementId(QueueType::SOCIAL_POST);
            if ($elementId === null) {
                break;
            }

            $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_INPROGRESS);

            $element = $this->structureManager->getElementById($elementId);
            if (!$element || !$element instanceof structureElement) {
                $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
                $this->logger->error("Social post element not found: $elementId");
                continue;
            }

            $startTime = microtime(true);
            $postDto = null;
            try {
                $postDto = $this->socialPostTransformer->transform($element);
                if ($postDto) {
                    $this->postService->sendPost($postDto);
                    $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SUCCESS);
                    $this->logger->info("Social post sent successfully for element $element->structureType $elementId: $element->title");
                } else {
                    $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SKIP);
                    $this->logger->info("Social post skipped for element $element->structureType $elementId: $element->title");
                }
            } catch (Exception $exception) {
                $postData = 'null';
                if ($postDto) {
                    $encoded = json_encode([
                        'title' => $postDto->title,
                        'link' => $postDto->link,
                        'image' => $postDto->image,
                        'audio' => $postDto->audio,
                        'description' => $postDto->description,
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if ($encoded !== false) {
                        $postData = $encoded;
                    }
                }

                $this->logger->error("Failed to send social post for element $element->structureType $elementId. Error: " . $exception->getMessage() . ". Data sent: " . $postData);
                $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
            }
            $endTime = microtime(true);
            $totalExecution += ($endTime - $startTime);
        }
    }
}
