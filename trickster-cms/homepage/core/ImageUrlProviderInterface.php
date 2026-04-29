<?php
declare(strict_types=1);

interface ImageUrlProviderInterface
{
    public function getImageId();

    public function getImageName();

    public function getImageUrl(string $preset = 'adminImage'): ?string;
}