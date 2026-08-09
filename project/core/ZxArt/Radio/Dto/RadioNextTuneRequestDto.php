<?php

declare(strict_types=1);

namespace ZxArt\Radio\Dto;

final readonly class RadioNextTuneRequestDto
{
    public function __construct(public ?RadioCriteriaDto $criteria = null)
    {
    }
}
