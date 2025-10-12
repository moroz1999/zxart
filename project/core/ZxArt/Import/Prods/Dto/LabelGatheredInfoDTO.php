<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

final class LabelGatheredInfoDTO
{
    public function __construct(
        public readonly ?bool $isAlias = null,
        public readonly ?bool $isPerson = null,
        public readonly ?bool $isGroup = null,
    )
    {
    }

    public static function fromArray(array $a): self
    {
        return new self(
            isAlias: array_key_exists('isAlias', $a) ? (bool)$a['isAlias'] : null,
            isPerson: array_key_exists('isPerson', $a) ? (bool)$a['isPerson'] : null,
            isGroup: array_key_exists('isGroup', $a) ? (bool)$a['isGroup'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'isAlias' => $this->isAlias,
            'isPerson' => $this->isPerson,
            'isGroup' => $this->isGroup,
        ], static fn($v) => $v !== null);
    }
}
