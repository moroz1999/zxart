<?php

trait GraphicsCompoProvider
{
    public function getCompoTypes()
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