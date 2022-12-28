<?php

trait ReleaseFormatsProvider
{
    public function getReleaseFormats()
    {
        return [
            'dsk',
            'tzx',
            'tap',
            'trd',
            'scl',
            'bin',
            'sna',
            'szx',
            'z80',
            'fdi',
            'udi',
            'td0',
            'rom',
            'spg',
            'mdr',
            'd80',
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
                'z80',
            ],
        ];
    }

}