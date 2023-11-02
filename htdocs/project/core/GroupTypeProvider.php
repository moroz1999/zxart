<?php

trait GroupTypeProvider
{
    public function getGroupTypes()
    {
        return [
            'unknown',
            'company',
            'studio',
            'scene',
            'education',
            'store',
            'science',
        ];
    }
}