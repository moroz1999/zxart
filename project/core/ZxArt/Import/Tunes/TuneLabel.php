<?php
declare(strict_types=1);


namespace ZxArt\Import\Tunes;

final readonly class TuneLabel
{
    public function __construct(
        public string $title,
        public ?int   $year = null,
    )
    {

    }
}