<?php

trait GroupTypeProvider
{
    /**
     * @return string[]
     *
     * @psalm-return list{'unknown', 'company', 'crack', 'studio', 'scene', 'education', 'store', 'science'}
     */
    public function getGroupTypes(): array
    {
        return [
            'unknown',
            'company',
            'crack',
            'studio',
            'scene',
            'education',
            'store',
            'science',
        ];
    }
}