<?php

trait GraphicsCompoProvider
{
    /**
     * @return string[]
     *
     * @psalm-return list{'standard', 'gigascreen', 'alternative', 'realtime', 'realtimep', 'online', 'onlineattr', 'copy', 'nocopy', 'out', 'logo', 'paintover', 'related', '16c', '64c', '256c', 'wild', 'fakegame', 'textmode'}
     */
    public function getCompoTypes(): array
    {
        return [
            'standard',
            'gigascreen',
            'alternative',
            'realtime',
            'realtimep',
            'online',
            'onlineattr',
            'copy',
            'nocopy',
            'out',
            'logo',
            'paintover',
            'related',
            '16c',
            '64c',
            '256c',
            'wild',
            'fakegame',
            'textmode',
        ];
    }
}