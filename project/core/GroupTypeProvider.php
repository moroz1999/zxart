<?php

trait GroupTypeProvider
{
    public function getGroupTypes()
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