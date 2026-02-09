<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use commentElement;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use ZxArt\Comments\CommentsTransformer;
use ZxArt\Comments\Exception\CommentOperationException;

class CommentsTransformerTest extends TestCase
{
    private CommentsTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new CommentsTransformer(
            $this->createMock(privilegesManager::class),
        );
    }

    public function testTransformToDtoThrowsOnEmptyAuthorName(): void
    {
        $comment = $this->createMock(commentElement::class);
        $comment->method('getUserElement')->willReturn(false);
        $comment->method('getAuthorName')->willReturn('');

        $this->expectException(CommentOperationException::class);
        $this->expectExceptionMessage('has no author');
        $this->transformer->transformToDto($comment);
    }
}
