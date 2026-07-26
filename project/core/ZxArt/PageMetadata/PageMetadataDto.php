<?php

declare(strict_types=1);

namespace ZxArt\PageMetadata;

readonly class PageMetadataDto
{
    /**
     * @param array<string, string> $openGraph
     * @param array<string, string> $twitter
     * @param array<string, string> $languageLinks
     * @param array<array-key, mixed>|null $structuredData
     */
    public function __construct(
        public string $title,
        public string $description,
        public bool $noIndex,
        public array $openGraph,
        public array $twitter,
        public array $languageLinks,
        public ?array $structuredData,
    ) {
    }
}
