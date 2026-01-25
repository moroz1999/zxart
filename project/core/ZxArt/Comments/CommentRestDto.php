<?php
declare(strict_types=1);

namespace ZxArt\Comments;

class CommentRestDto
{
    public int $id;
    public string $author;
    public ?string $authorUrl;
    public ?string $authorBadge;
    public string $date;
    public string $content;
    public int $votes;
    public bool $votingDenied;
    public bool $commentsAllowed;
    /** @var CommentRestDto[] */
    public array $children = [];
    public ?int $parentId;
}
