<?php

trait MusicSettingsProvider
{
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
            '1750000',
            '1770000',
            '1773400',
            '1789770',
            '2000000',
            '3500000',
        ];
    }
//    public function getIntFrequencies()
//    {
//        return [
//            '1750000',
//            '1770000',
//            '1773400',
//            '1789770',
//            '2000000',
//            '3500000',
//        ];
//    }

}