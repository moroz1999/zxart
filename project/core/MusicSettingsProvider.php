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
     * Sound groups a tune can be assigned to — the chip and playback setup it
     * was written for. Chosen by the uploader, shown on the tune page and used
     * by the music search, the author music tab and the radio. The SPA labels
     * the bare codes itself.
     *
     * @return string[]
     *
     * @psalm-return list{'ay', 'beeper', 'digitalbeeper', 'beeperdigitalbeeper', 'digitalay', 'ts', 'fm', 'tsfm', 'aybeeper', 'aydigitalay', 'aycovox', 'saa'}
     */
    public function getFormatGroups(): array
    {
        return [
            'ay',
            'beeper',
            'digitalbeeper',
            'beeperdigitalbeeper',
            'digitalay',
            'ts',
            'fm',
            'tsfm',
            'aybeeper',
            'aydigitalay',
            'aycovox',
            'saa',
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