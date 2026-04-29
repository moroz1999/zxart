<?php


interface JsonDataProvider
{
    public function getElementData(?string $preset = null): ?array;

    public function getJsonInfo(?string $preset = null): ?string;
}