<?php
declare(strict_types=1);

namespace ZxArt\Import\Prods\Dto;

final readonly class PartyRefDTO
{
    public function __construct(
        public string  $title,
        public ?string $website = null,
        public ?int    $year = null,
        public ?int    $place = null,
    )
    {
    }

    public static function fromArray(?array $data): ?self
    {
        if (!$data) {
            return null;
        }

        return new self(
            title: (string)($data['title'] ?? ''),
            website: isset($data['website']) && $data['website'] !== '' ? (string)$data['website'] : null,
            year: isset($data['year']) && $data['year'] !== '' ? (int)$data['year'] : null,
            place: isset($data['place']) && $data['place'] !== '' ? (int)$data['place'] : null,
        );
    }
}
