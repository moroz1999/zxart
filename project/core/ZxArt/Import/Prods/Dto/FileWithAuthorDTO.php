<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

final class FileWithAuthorDTO
{
    public function __construct(
        public readonly string  $url,
        public readonly ?string $author = null,
    )
    {
    }

    public static function fromArray(array $a): self
    {
        return new self(
            url: (string)$a['url'],
            author: isset($a['author']) && $a['author'] !== '' ? (string)$a['author'] : null,
        );
    }
}
