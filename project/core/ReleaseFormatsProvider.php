<?php

trait ReleaseFormatsProvider
{
    public function getReleaseFormats(): array
    {
        return [
            'dsk',
            'trd',
            'scl',
            'fdi',
            'udi',
            'td0',
            'd80',
            'mgt',
            'opd',
            'mbd',
            'img',

            'tzx',
            'tap',
            'mdr',
            'p',

            'bin',
            'rom',
            'spg',
            'nex',
            'snx',

            'sna',
            'szx',
            'dck',
            'z80',
            'slt',
        ];
    }

    public function getGroupedReleaseFormats(): array
    {
        return [
            'disk' => [
                'dsk',
                'trd',
                'scl',
                'fdi',
                'udi',
                'td0',
                'd80',
                'mgt',
                'opd',
                'mbd',
                'img',
            ],

            'tape' => [
                'tzx',
                'tap',
                'mdr',
                'p',
            ],

            'rom' => [
                'bin',
                'rom',
                'spg',
                'nex',
                'snx',
            ],

            'snapshot' => [
                'sna',
                'szx',
                'dck',
                'z80',
                'slt',
            ],
        ];
    }

}