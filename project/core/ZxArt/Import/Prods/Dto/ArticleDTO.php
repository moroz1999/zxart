<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

final class ArticleDTO
{
    public function __construct(
        public readonly string  $title,
        public readonly string  $introduction = '',
        public readonly ?string $externalLink = null,
        public readonly string  $content,
    )
    {
    }

    public static function fromArray(array $a): self
    {
        return new self(
            title: (string)($a['title'] ?? ''),
            introduction: (string)($a['introduction'] ?? ''),
            externalLink: $a['externalLink'] ? (string)$a['externalLink'] : null,
            content: (string)($a['content'] ?? ''),
        );
    }
}
