<?php

class PicturesModesManager
{
    public function getModeInfo(): array
    {
        return [
            'mode' => 'mix',
            'border' => '1',
            'hidden' => '0',
        ];
    }

    public function getMode(): string
    {
        return 'mix';
    }

    public function getBorder(): string
    {
        return '1';
    }

    public function getHidden(): string
    {
        return '0';
    }
}
