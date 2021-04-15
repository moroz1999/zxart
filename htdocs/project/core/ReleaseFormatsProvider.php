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
            ],
            'tape' => [
                'tzx',
                'tap',
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