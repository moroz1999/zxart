<?php
declare(strict_types=1);

namespace ZxArt\Rss;

readonly class RssDto
{
    public function __construct(
        public string $title,
        public string $link,
        public string $description,
        public string $content,
        public string $date,
        public string $guid,
    ) {
    }
}
