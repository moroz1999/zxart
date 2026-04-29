<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Cache;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Comments\CommentsService;
use ZxArt\Comments\CommentsTransformer;
use ZxArt\Comments\Exception\CommentAccessDeniedException;
use ZxArt\Comments\Exception\CommentOperationException;
use ZxArt\Comments\Repositories\CommentsRepository;

#[AllowMockObjectsWithoutExpectations]
class CommentsServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private CurrentUserService&MockObject $currentUserService;
    private CurrentUser&MockObject $user;
    private CommentsService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->user = $this->getMockBuilder(CurrentUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAuthorized', 'refreshPrivileges', '__destruct', 'writeStorage'])
            ->getMock();
        $this->currentUserService = $this->createMock(CurrentUserService::class);
        $this->currentUserService->method('getCurrentUser')->willReturn($this->user);

        $this->service = new CommentsService(
            structureManager: $this->structureManager,
            currentUserService: $this->currentUserService,
            languagesManager: $this->createMock(LanguagesManager::class),
            privilegesManager: $this->createMock(privilegesManager::class),
            cache: $this->createMock(Cache::class),
            transformer: $this->createMock(CommentsTransformer::class),
            commentsRepository: $this->createMock(CommentsRepository::class),
        );
    }

    public function testAddCommentRejectsUnauthorizedUser(): void
    {
        $this->user->method('isAuthorized')->willReturn(false);

        $this->expectException(CommentAccessDeniedException::class);
        $this->service->addComment(1, 'Some content');
    }

    public function testAddCommentRejectsEmptyContent(): void
    {
        $this->user->method('isAuthorized')->willReturn(true);

        $this->expectException(CommentOperationException::class);
        $this->expectExceptionMessage('empty');
        $this->service->addComment(1, '');
    }

    public function testAddCommentRejectsWhitespaceOnlyContent(): void
    {
        $this->user->method('isAuthorized')->willReturn(true);

        $this->expectException(CommentOperationException::class);
        $this->expectExceptionMessage('empty');
        $this->service->addComment(1, '   ');
    }
}
