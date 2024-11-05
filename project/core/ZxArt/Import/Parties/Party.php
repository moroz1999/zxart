<?php
declare(strict_types=1);

namespace ZxArt\Import\Parties;

readonly final class Party
{
    public function __construct(
        public ?string $id = null,
        public ?string $title = null,
        public ?string $city = null,
        public ?string $country = null,
        public ?int    $year = null,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'year' => $this->year,
            'city' => $this->year,
            'country' => $this->year,
        ];
    }
}
