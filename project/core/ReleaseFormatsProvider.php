<?php

trait ReleaseFormatsProvider
{
    /**
     * @return string[]
     *
     * @psalm-return list{'dsk', 'trd', 'scl', 'fdi', 'udi', 'td0', 'd80', 'mgt', 'tzx', 'tap', 'mdr', 'bin', 'rom', 'spg', 'nex', 'sna', 'szx', 'dck', 'z80'}
     */
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

            'tzx',
            'tap',
            'mdr',

            'bin',
            'rom',
            'spg',
            'nex',
            'snx',

            'sna',
            'szx',
            'dck',
            'z80',

            'p',

        ];
    }

    /**
     * @return string[][]
     *
     * @psalm-return array{disk: list{'dsk', 'trd', 'scl', 'fdi', 'udi', 'td0', 'd80', 'mgt'}, tape: list{'tzx', 'tap', 'mdr'}, rom: list{'bin', 'rom', 'spg', 'nex'}, snapshot: list{'sna', 'szx', 'dck', 'z80'}}
     */
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
                'nex',
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