<?php
declare(strict_types=1);


namespace ZxArt\Import\Pictures;

final readonly class PictureLabel
{
    public function __construct(
        public string $title,
        public ?int   $year = null,
    )
    {

    }
}