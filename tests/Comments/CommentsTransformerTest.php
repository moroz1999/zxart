<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use commentElement;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use userElement;
use ZxArt\Comments\CommentsTransformer;
use ZxArt\Urls\EntityUrlResolver;

#[AllowMockObjectsWithoutExpectations]
class CommentsTransformerTest extends TestCase
{
    private CommentsTransformer $transformer;
    private privilegesManager $privilegesManager;

    protected function setUp(): void
    {
        $this->privilegesManager = $this->createMock(privilegesManager::class);
        $this->privilegesManager->method('checkPrivilegesForAction')->willReturn(false);

        $currentUser = $this->getMockBuilder(CurrentUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__destruct', 'writeStorage'])
            ->getMock();
        $currentUser->id = 0;
        $currentUserService = $this->createMock(CurrentUserService::class);
        $currentUserService->method('getCurrentUser')->willReturn($currentUser);

        $entityUrlResolver = $this->createMock(EntityUrlResolver::class);
        $entityUrlResolver->method('urlFor')->willReturn('/author/42');
        $this->transformer = new CommentsTransformer(
            $entityUrlResolver,
            $this->privilegesManager,
            $currentUserService,
        );
    }

    public function testTransformToDtoReturnsNullAuthorWhenAuthorNameIsEmpty(): void
    {
        $comment = $this->createMock(commentElement::class);
        $comment->method('getUserElement')->willReturn(false);
        $comment->method('getAuthorName')->willReturn('');
        $comment->method('getDecoratedContent')->willReturn('<p>content</p>');
        $comment->method('getValue')->with('content')->willReturn('<p>content</p>');
        $comment->method('isEditable')->willReturn(false);
        $comment->method('getInitialTarget')->willReturn(null);
        $comment->method('getParentElement')->willReturn(null);

        $dto = $this->transformer->transformToDto($comment);

        $this->assertSame(0, $dto->id);
        $this->assertNull($dto->author);
        $this->assertSame('', $dto->date);
        $this->assertSame('<p>content</p>', $dto->content);
        $this->assertSame('content', $dto->originalContent);
        $this->assertFalse($dto->canEdit);
        $this->assertFalse($dto->canDelete);
        $this->assertNull($dto->target);
        $this->assertNull($dto->parentId);
        $this->assertSame([], $dto->children);
    }

    public function testTransformToDtoUsesSpaUrlForConnectedAuthor(): void
    {
        $author = $this->createMock(structureElement::class);
        $user = $this->createMock(userElement::class);
        $user->method('getAuthorElement')->willReturn($author);
        $user->method('getBadgetTypes')->willReturn(['vip']);
        $user->method('getTitle')->willReturn('Test user');

        $comment = $this->createMock(commentElement::class);
        $comment->method('getUserElement')->willReturn($user);
        $comment->method('getDecoratedContent')->willReturn('<p>content</p>');
        $comment->method('getValue')->with('content')->willReturn('<p>content</p>');
        $comment->method('isEditable')->willReturn(false);
        $comment->method('getInitialTarget')->willReturn(null);
        $comment->method('getParentElement')->willReturn(null);

        $dto = $this->transformer->transformToDto($comment);

        $this->assertNotNull($dto->author);
        $this->assertSame('Test user', $dto->author->name);
        $this->assertSame('/author/42', $dto->author->url);
        $this->assertSame(['vip'], $dto->author->badges);
    }
}
