<?php

declare(strict_types=1);

namespace ZxArt\Social;

use Exception;
use Psr\Log\LoggerInterface;
use structureManager;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Telegram\PostDto;
use ZxArt\Telegram\PostService;
use zxMusicElement;
use zxPictureElement;
use zxReleaseElement;

readonly class SocialPostsService
{
    public function __construct(
        private PostService      $postService,
        private QueueService     $queueService,
        private structureManager $structureManager,
        private LoggerInterface  $logger,
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
            if (!$element) {
                $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
                $this->logger->error("Social post element not found: $elementId");
                continue;
            }

            $startTime = microtime(true);
            try {
                $postDto = $this->createPostDto($element);
                if ($postDto) {
                    $this->postService->sendPost($postDto);
                    $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SUCCESS);
                    $this->logger->info("Social post sent successfully for element $elementId: $element->title");
                } else {
                    $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SKIP);
                    $this->logger->info("Social post skipped for element $elementId: $element->title");
                }
            } catch (Exception $exception) {
                $this->logger->error("Failed to send social post for element $elementId: " . $exception->getMessage());
                $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_FAIL);
            }
            $endTime = microtime(true);
            $totalExecution += ($endTime - $startTime);
        }
    }

    private function createPostDto(object $element): ?PostDto
    {
        if ($element instanceof zxReleaseElement) {
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: (string)$element->getImageUrl(1),
                description: html_entity_decode($element->getTextContent(), ENT_QUOTES),
            );
        }
        if ($element instanceof zxMusicElement) {
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: null,
                description: html_entity_decode((string)$element->getTextContent(), ENT_QUOTES),
            );
        }
        if ($element instanceof zxPictureElement) {
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: $element->getImageUrl(3, false),
                description: html_entity_decode($element->getTextContent(), ENT_QUOTES),
            );
        }

        return null;
    }
}
