<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use UnexpectedValueException;
use ZxArt\Comments\CommentTranslationAiService;
use ZxArt\Comments\CommentTranslationCandidateDto;
use ZxArt\Comments\CommentTranslationDto;
use ZxArt\Comments\CommentTranslationService;
use ZxArt\Comments\LatestCommentsCache;
use ZxArt\Comments\Repositories\CommentsRepository;

#[AllowMockObjectsWithoutExpectations]
class CommentTranslationServiceTest extends TestCase
{
    private CommentsRepository&MockObject $commentsRepository;
    private CommentTranslationAiService&MockObject $aiService;
    private structureManager&MockObject $structureManager;
    private LatestCommentsCache&MockObject $latestCommentsCache;
    private CommentTranslationService $service;

    protected function setUp(): void
    {
        $this->commentsRepository = $this->createMock(CommentsRepository::class);
        $this->aiService = $this->createMock(CommentTranslationAiService::class);
        $this->structureManager = $this->createMock(structureManager::class);
        $this->latestCommentsCache = $this->createMock(LatestCommentsCache::class);

        $this->service = new CommentTranslationService(
            commentsRepository: $this->commentsRepository,
            aiService: $this->aiService,
            structureManager: $this->structureManager,
            latestCommentsCache: $this->latestCommentsCache,
        );
    }

    public function testTranslatedCommentDropsItsElementCache(): void
    {
        $this->commentsRepository->method('getUntranslatedComments')
            ->willReturn([new CommentTranslationCandidateDto(id: 42, text: 'Привет')]);
        $this->aiService->method('translate')
            ->willReturn(new CommentTranslationDto('Hi', 'Привет', 'Hola', 'ru'));

        $this->structureManager->expects($this->once())
            ->method('clearElementCache')
            ->with(42);

        $this->service->translateNextBatch();
    }

    public function testFailedTranslationKeepsElementCache(): void
    {
        $this->commentsRepository->method('getUntranslatedComments')
            ->willReturn([new CommentTranslationCandidateDto(id: 42, text: 'Привет')]);
        $this->aiService->method('translate')
            ->willThrowException(new UnexpectedValueException('broken'));

        $this->structureManager->expects($this->never())
            ->method('clearElementCache');

        $this->service->translateNextBatch();
    }

    public function testBatchWithTranslationsClearsLatestCommentsCacheOnce(): void
    {
        $this->commentsRepository->method('getUntranslatedComments')->willReturn([
            new CommentTranslationCandidateDto(id: 42, text: 'Привет'),
            new CommentTranslationCandidateDto(id: 43, text: 'Пока'),
        ]);
        $this->aiService->method('translate')
            ->willReturn(new CommentTranslationDto('Hi', 'Привет', 'Hola', 'ru'));

        $this->latestCommentsCache->expects($this->once())->method('clear');

        $this->service->translateNextBatch();
    }

    public function testBatchWithoutTranslationsKeepsLatestCommentsCache(): void
    {
        $this->commentsRepository->method('getUntranslatedComments')
            ->willReturn([new CommentTranslationCandidateDto(id: 42, text: 'Привет')]);
        $this->aiService->method('translate')
            ->willThrowException(new UnexpectedValueException('broken'));

        $this->latestCommentsCache->expects($this->never())->method('clear');

        $this->service->translateNextBatch();
    }
}
