<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Dto;

use ZxArt\Comments\CommentAuthorDto;

readonly class RecentRatingDto
{
    public function __construct(
        public CommentAuthorDto $user,
        public string $rating,
        public string $targetTitle,
        public string $targetUrl,
    ) {
    }
}
