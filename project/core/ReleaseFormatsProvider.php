<?php

trait ReleaseFormatsProvider
{
    /**
     * Centralized definition of all release formats grouped by category
     */
    private const FORMATS = [
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
            'd40',
            'd80',
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
            '$b',
            '$c',
        ],
    ];

    /**
     * Returns all release formats as a flat list
     */
    public function getReleaseFormats(): array
    {
        return array_merge(...array_values(self::FORMATS));
    }

    /**
     * Returns release formats grouped by type
     */
    public function getGroupedReleaseFormats(): array
    {
        return self::FORMATS;
    }
}
