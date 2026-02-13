<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use commentElement;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use ZxArt\Comments\CommentsTransformer;

class CommentsTransformerTest extends TestCase
{
    private CommentsTransformer $transformer;
    private privilegesManager $privilegesManager;

    protected function setUp(): void
    {
        $this->privilegesManager = $this->createMock(privilegesManager::class);
        $this->privilegesManager->method('checkPrivilegesForAction')->willReturn(false);

        $this->transformer = new CommentsTransformer(
            $this->privilegesManager,
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
}
