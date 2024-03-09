<?php

trait DemoCompoTypesProvider
{
    /**
     * @return string[]
     *
     * @psalm-return list{'2d_demo', 'basic', 'demo', 'enhanced', 'intro', 'intro8', 'intro16', 'intro32', 'intro64', 'intro128', 'intro256', 'intro512', 'intro1k', 'intro4k', 'invitation', 'procedural1k', 'procedural4k', 'realtime_coding', 'game', 'basic_game', 'wild', 'related', 'graphics', 'out', 'gravedigger', 'musicdisk', 'utility', 'onescene', 'amigademo'}
     */
    public function getCompoTypes(): array
    {
        return [
            '2d_demo',
            'basic',
            'demo',
            'enhanced',
            'intro',
            'intro8',
            'intro16',
            'intro32',
            'intro64',
            'intro128',
            'intro256',
            'intro512',
            'intro1k',
            'intro4k',
            'invitation',
            'procedural1k',
            'procedural4k',
            'realtime_coding',
            'game',
            'basic_game',
            'wild',
            'related',
            'graphics',
            'out',
            'gravedigger',
            'musicdisk',
            'utility',
            'onescene',
            'amigademo',
        ];
    }
}









