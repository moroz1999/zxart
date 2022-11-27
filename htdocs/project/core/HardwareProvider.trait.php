<?php

trait HardwareProviderTrait
{
    public function getHardwareList(): array
    {
        return [
            "computers" => [
                'zx48',
                "zx16",
                'zx128',
                "zx128+2",
                "zx128+2b",
                "zx128+3",
                "sinclairql",
                "timex2048",
                "timex2068",
                "atm",
                "atm2",
                "pentagon128",
                "pentagon512",
                "pentagon1024",
                "profi",
                "scorpion",
                "byte",
                "zxmphoenix",
                "zxevolution",
                "zxnext",
                "elementzxmb",
                "zxuno",
                "samcoupe",
                "zx80",
                "zx8116",
                "zx811",
                "zx812",
                "zx8132",
                "zx8164",
            ],
            "configuration" => [
                'baseconf',
                'tsconf',
            ],
            "storage" => [
                'tape',
                '3dosdisk',
                'betadisk',
                'hdd',
                'divsd',
            ],
            "sound" => [
                'ay',
                'beeper',
                'ts',
                'tsfm',
                'gs',
                'ngs',
                'covox',
                'soundrive',
                'specdrum',
                'cheetah',
                'dmausc',
                'saa',
                'zxm',
            ],
            "controls" => [
                'cursor',
                'kempston',
                'int2_1',
                'int2_2',
                'kempstonmouse',
                'aymouse',
            ],
            "expansion" => [
                'smuc',
                'zxpand',
                'Cache',
            ],
        ];
    }

}