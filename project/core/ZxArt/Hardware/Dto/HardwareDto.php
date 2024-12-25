<?php
declare(strict_types=1);

namespace ZxArt\Hardware\Dto;

final class HardwareDto
{
    public function __construct(
        public int    $articleId,
        public string $hardwareId,
        public string $json,
    )
    {

    }
}