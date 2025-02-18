<?php

trait HardwareProvider
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
                "pentagon2666",
                "profi",
                "scorpion",
                "scorpion1024",
                "byte",
                "zxmphoenix",
                'baseconf',
                'tsconf',
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
                "lambda8300",
                "sprinter",
                "alf",
            ],
            "storage" => [
                'tape',
                '3dosdisk',
                'betadisk',
                'cd',
                'profiide',
                'nemoide',
                'zcontroller',
                'atmide',
                'divide',
                'divmmc',
                'smuc',
                'sddivmmc',
                'sdz',
                'sdneogs',
            ],
            "dos" => [
                'trdos',
                'isdos',
                'tasis',
                'cpm',
                'esxdos',
                'mdos',
                '3dos',
                'nedoos',
                'opd',
                'disciple',
            ],
            "sound" => [
                'ay',
                'beeper',
                'ts',
                'tsfm',
                'gs',
                'ngs',
                'covoxfb',
                'covoxdd',
                'soundrive',
                'specdrum',
                'cheetah',
                'dmausc',
                'saa',
                'zxm',
                'sid',
                'sidelzx',
            ],
            "controls" => [
                'cursor',
                'kempston',
                'kempston8b',
                'int2_1',
                'int2_2',
                'kempstonmouse',
                'aymouse',
                'gunstick',
                'magnumlight',
                'novina',
                'lightpen',
            ],
            "expansion" => [
                'zxpand',
                'cache',
                'gmx',
                'flashcolor',
                'ulaplus',
                'radastan',
            ],
        ];
    }

    public function getHardwareType($item): ?string
    {
        foreach ($this->getHardwareList() as $type => $list) {
            if (in_array($item, $list, true)) {
                return $type;
            }
        }
        return null;
    }
}