<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use App\Users\CurrentUser;
use Cache;
use Illuminate\Database\Connection;
use LanguagesManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Comments\CommentsService;
use ZxArt\Comments\CommentsTransformer;
use ZxArt\Comments\Exception\CommentAccessDeniedException;
use ZxArt\Comments\Exception\CommentOperationException;

class CommentsServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private CurrentUser&MockObject $user;
    private CommentsService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->user = $this->getMockBuilder(CurrentUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAuthorized', 'refreshPrivileges', '__destruct', 'writeStorage'])
            ->getMock();

        $this->service = new CommentsService(
            structureManager: $this->structureManager,
            user: $this->user,
            languagesManager: $this->createMock(LanguagesManager::class),
            privilegesManager: $this->createMock(privilegesManager::class),
            cache: $this->createMock(Cache::class),
            transformer: $this->createMock(CommentsTransformer::class),
            db: $this->createMock(Connection::class),
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
