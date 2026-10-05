<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use commentElement;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureManager;
use ZxArt\Comments\CommentContentPurifier;
use ZxArt\Comments\CommentDto;
use ZxArt\Comments\CommentsService;
use ZxArt\Comments\CommentsTransformer;
use ZxArt\Comments\Exception\CommentAccessDeniedException;
use ZxArt\Comments\Exception\CommentOperationException;
use ZxArt\Comments\LatestCommentsCache;
use ZxArt\Comments\Repositories\CommentsRepository;
use zxPictureElement;

#[AllowMockObjectsWithoutExpectations]
class CommentsServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private CurrentUserService&MockObject $currentUserService;
    private CurrentUser&MockObject $user;
    private CommentsRepository&MockObject $commentsRepository;
    private privilegesManager&MockObject $privilegesManager;
    private LatestCommentsCache&MockObject $latestCommentsCache;
    private CommentsTransformer&MockObject $transformer;
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
        $this->commentsRepository = $this->createMock(CommentsRepository::class);
        $this->privilegesManager = $this->createMock(privilegesManager::class);
        $this->latestCommentsCache = $this->createMock(LatestCommentsCache::class);
        $this->transformer = $this->createMock(CommentsTransformer::class);

        $this->service = new CommentsService(
            structureManager: $this->structureManager,
            currentUserService: $this->currentUserService,
            privilegesManager: $this->privilegesManager,
            latestCommentsCache: $this->latestCommentsCache,
            transformer: $this->transformer,
            commentsRepository: $this->commentsRepository,
            contentPurifier: new CommentContentPurifier(),
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

    public function testAddCommentRejectsContentThatIsOnlyMarkup(): void
    {
        $this->user->method('isAuthorized')->willReturn(true);

        $this->expectException(CommentOperationException::class);
        $this->expectExceptionMessage('empty');
        $this->service->addComment(1, '<script>alert(1)</script>');
    }

    public function testAddCommentRecalculatesTargetComments(): void
    {
        $this->user->method('isAuthorized')->willReturn(true);
        $work = $this->createMock(zxPictureElement::class);
        $work->method('areCommentsAllowed')->willReturn(true);
        $this->structureManager->method('getElementById')->with(1)->willReturn($work);
        $this->structureManager->method('createElement')->willReturn($this->createMock(commentElement::class));

        $work->expects($this->once())->method('recalculateComments');

        $this->service->addComment(1, 'Nice one');
    }

    public function testAddReplyRecalculatesCommentsOfTheInitialTarget(): void
    {
        $this->user->method('isAuthorized')->willReturn(true);
        $work = $this->createMock(zxPictureElement::class);
        $parentComment = $this->createMock(commentElement::class);
        $parentComment->method('areCommentsAllowed')->willReturn(true);
        $parentComment->method('getInitialTarget')->willReturn($work);
        $this->structureManager->method('getElementById')->with(5)->willReturn($parentComment);
        $this->structureManager->method('createElement')->willReturn($this->createMock(commentElement::class));

        $work->expects($this->once())->method('recalculateComments');

        $this->service->addComment(5, 'Agreed');
    }

    public function testDeleteCommentRecalculatesCommentsOfTheInitialTarget(): void
    {
        $work = $this->createMock(zxPictureElement::class);
        $comment = $this->createMock(commentElement::class);
        $comment->method('getInitialTarget')->willReturn($work);
        $this->structureManager->method('getElementById')->with(7)->willReturn($comment);
        $this->privilegesManager->method('checkPrivilegesForAction')->willReturn(true);

        $work->expects($this->once())->method('recalculateComments');

        $this->service->deleteComment(7);
    }

    public function testLatestCommentsAreServedFromCache(): void
    {
        $cached = [new CommentDto(1, null, '', 'text', 'text', '', false, false)];
        $this->latestCommentsCache->method('get')->with('eng')->willReturn($cached);

        $this->commentsRepository->expects($this->never())->method('getLatestIds');

        $this->assertSame($cached, $this->service->getLatestComments(10, 'eng'));
    }

    public function testLatestCommentsMissIsBuiltReadOnlyAndStored(): void
    {
        $comment = $this->createMock(commentElement::class);
        $dto = new CommentDto(3, null, '', 'text', 'text', '', false, false);
        $this->latestCommentsCache->method('get')->willReturn(null);
        $this->commentsRepository->method('getLatestIds')->with(10)->willReturn([3]);
        $this->structureManager->method('getElementById')->with(3)->willReturn($comment);
        $this->transformer->method('transformToReadOnlyDto')->with($comment, 'eng')->willReturn($dto);

        $this->latestCommentsCache->expects($this->once())->method('set')->with('eng', [$dto]);

        $this->assertSame([$dto], $this->service->getLatestComments(10, 'eng'));
    }

    public function testLatestCommentsWithCustomLimitBypassCache(): void
    {
        $this->commentsRepository->method('getLatestIds')->willReturn([]);

        $this->latestCommentsCache->expects($this->never())->method('get');
        $this->latestCommentsCache->expects($this->never())->method('set');

        $this->service->getLatestComments(5, 'eng');
    }

    public function testAddCommentClearsLatestCommentsCache(): void
    {
        $this->user->method('isAuthorized')->willReturn(true);
        $work = $this->createMock(zxPictureElement::class);
        $work->method('areCommentsAllowed')->willReturn(true);
        $this->structureManager->method('getElementById')->willReturn($work);
        $this->structureManager->method('createElement')->willReturn($this->createMock(commentElement::class));

        $this->latestCommentsCache->expects($this->once())->method('clear');

        $this->service->addComment(1, 'Nice one');
    }

    public function testUpdateCommentClearsLatestCommentsCache(): void
    {
        $comment = $this->createMock(commentElement::class);
        $this->structureManager->method('getElementById')->willReturn($comment);
        $this->privilegesManager->method('checkPrivilegesForAction')->willReturn(true);

        $this->latestCommentsCache->expects($this->once())->method('clear');

        $this->service->updateComment(7, 'Edited');
    }

    public function testDeleteCommentClearsLatestCommentsCache(): void
    {
        $comment = $this->createMock(commentElement::class);
        $this->structureManager->method('getElementById')->willReturn($comment);
        $this->privilegesManager->method('checkPrivilegesForAction')->willReturn(true);

        $this->latestCommentsCache->expects($this->once())->method('clear');

        $this->service->deleteComment(7);
    }

    public function testAuthorCommentsUseRequestedPageSize(): void
    {
        $this->commentsRepository
            ->method('countByAuthorId')
            ->with(7)
            ->willReturn(40);
        $this->commentsRepository
            ->expects($this->once())
            ->method('getIdsByAuthorId')
            ->with(7, 10, 10)
            ->willReturn([]);

        $result = $this->service->getAuthorCommentsPaginated(7, 2, null, 10);

        $this->assertSame(2, $result->currentPage);
        $this->assertSame(4, $result->pagesAmount);
        $this->assertSame(40, $result->totalCount);
    }
}
