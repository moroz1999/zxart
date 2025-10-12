<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

final class PartyRefDTO
{
    public function __construct(
        public readonly string  $title,
        public readonly ?int    $year = null,
        public readonly ?int    $place = null,
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if (!$data) {
            return null;
        }

        return new self(
            title: (string)($data['title'] ?? ''),
            year: isset($data['year']) && $data['year'] !== '' ? (int)$data['year'] : null,
            place: isset($data['place']) && $data['place'] !== '' ? (int)$data['place'] : null,
        );
    }
}
