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
            'sad',

            'tzx',
            'tap',
            'mdr',
            'p',
            'o',

            'bin',
            'rom',
            'spg',
            'nex',
            'snx',
            'tar',

            'sna',
            'szx',
            'dck',
            'z80',
            'z81',
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
                'sad',
            ],

            'tape' => [
                'tzx',
                'tap',
                'mdr',
                'p',
                'o',
            ],

            'rom' => [
                'bin',
                'rom',
                'spg',
                'nex',
                'snx',
                'tar',
            ],

            'snapshot' => [
                'sna',
                'szx',
                'dck',
                'z80',
                'z81',
                'slt',
            ],
        ];
    }

}