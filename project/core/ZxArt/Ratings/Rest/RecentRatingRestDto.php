<?php
declare(strict_types=1);

namespace ZxArt\Ratings\Rest;

use Symfony\Component\ObjectMapper\Attribute\Map;
use ZxArt\Comments\CommentAuthorDto;

readonly class RecentRatingRestDto
{
    public function __construct(
        #[Map]
        public CommentAuthorDto $user,
        public string $rating,
        public string $targetTitle,
        public string $targetUrl,
    ) {
    }
}
