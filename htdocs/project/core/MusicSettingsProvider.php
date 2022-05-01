<?php

trait MusicSettingsProvider
{
    public function getCompoTypes()
    {
        return [
            'standard',
            'ay',
            'beeper',
            'copyay',
            'nocopyay',
            'realtime',
            'realtimeay',
            'realtimebeeper',
            'out',
            'wild',
            'experimental',
            'oldschool',
            'mainstream',
            'progressive',
            'ts',
            'tsfm',
            'related',
        ];
    }

    public function getChipTypes()
    {
        return [
            'ay',
            'ym',
        ];
    }

    public function getChannelsTypes()
    {
        return [
            'ABC',
            'ACB',
            'BAC',
            'BCA',
            'CBA',
            'CAB',
            'mono',
        ];
    }

    public function getFrequencies()
    {
        return [
            '1714286',
            '1750000',
            '1770000',
            '1773400',
            '1789770',
            '2000000',
            '3500000',
        ];
    }

    public function getIntFrequencies()
    {
        return [
            '48.828125',
            '50',
            '60',
            '100',
            '200',
            '400',
            '1000',
        ];
    }

}