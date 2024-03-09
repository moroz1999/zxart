<?php

trait PaletteTypesProvider
{
    /**
     * @return string[]
     *
     * @psalm-return list{'srgb', 'pulsar', 'alone', 'electroscale'}
     */
    public function getPaletteTypes(): array
    {
        return [
            'srgb',
            'pulsar',
            'alone',
            'electroscale',
        ];
    }
}