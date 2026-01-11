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
use zxMusicElement;
use zxPictureElement;
use zxProdElement;
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
            if (!$element || !$element instanceof structureElement) {
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
                    /** @psalm-suppress UndefinedMagicPropertyFetch */
                    $this->logger->info("Social post sent successfully for element $elementId: $element->title");
                } else {
                    $this->queueService->updateStatus($elementId, QueueType::SOCIAL_POST, QueueStatus::STATUS_SKIP);
                    /** @psalm-suppress UndefinedMagicPropertyFetch */
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

    private function createPostDto(structureElement $element): ?PostDto
    {
        if ($element instanceof zxReleaseElement) {
            /** @var string|string[] $description */
            $description = $element->getTextContent();
            if (is_array($description)) {
                $description = implode(' ', $description);
            }
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: (string)$element->getImageUrl(0),
                description: html_entity_decode($description, ENT_QUOTES),
            );
        }
        if ($element instanceof zxMusicElement) {
            $description = $element->getTextContent();
            if (is_array($description)) {
                $description = implode(' ', $description);
            }
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: null,
                description: html_entity_decode($description, ENT_QUOTES),
            );
        }
        if ($element instanceof zxPictureElement) {
            /** @var string|string[] $description */
            $description = $element->getTextContent();
            if (is_array($description)) {
                $description = implode(' ', $description);
            }
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: $element->getImageUrl(3, false),
                description: html_entity_decode($description, ENT_QUOTES),
            );
        }
        if ($element instanceof zxProdElement) {
            /** @var string|string[] $description */
            $description = $element->getMetaDescription();
            if (is_array($description)) {
                $description = implode(' ', $description);
            }
            return new PostDto(
                title: html_entity_decode((string)$element->getTitle(), ENT_QUOTES),
                link: (string)$element->getCanonicalUrl(),
                image: (string)$element->getImageUrl(0),
                description: html_entity_decode($description, ENT_QUOTES),
            );
        }

        return null;
    }
}
