<?php

trait ReleaseFormatsProvider
{
    public function getReleaseFormats()
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

            'tzx',
            'tap',
            'mdr',

            'bin',
            'rom',
            'spg',

            'sna',
            'szx',
            'dck',
            'z80',

        ];
    }

    public function getGroupedReleaseFormats()
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
            ],

            'tape' => [
                'tzx',
                'tap',
                'mdr',
            ],

            'rom' => [
                'bin',
                'rom',
                'spg',
            ],

            'snapshot' => [
                'sna',
                'szx',
                'dck',
                'z80',
            ],
        ];
    }

}