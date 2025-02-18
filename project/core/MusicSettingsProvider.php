<?php

trait MusicSettingsProvider
{
    /**
     * @return string[]
     *
     * @psalm-return list{'standard', 'ay', 'beeper', 'copyay', 'nocopyay', 'realtime', 'realtimeay', 'realtimebeeper', 'out', 'wild', 'experimental', 'oldschool', 'mainstream', 'progressive', 'ts', 'tsfm', 'related'}
     */
    public function getCompoTypes(): array
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

    /**
     * @return string[]
     *
     * @psalm-return list{'ay', 'ym'}
     */
    public function getChipTypes(): array
    {
        return [
            'ay',
            'ym',
        ];
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'ABC', 'ACB', 'BAC', 'BCA', 'CBA', 'CAB', 'mono'}
     */
    public function getChannelsTypes(): array
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

    public function getFrequencies(): array
    {
        return [
            '750000',
            '1714286',
            '1750000',
            '1770000',
            '1773400',
            '1789770',
            '2000000',
            '3500000',
        ];
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'48.828125', '50', '60', '100', '200', '400', '1000'}
     */
    public function getIntFrequencies(): array
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