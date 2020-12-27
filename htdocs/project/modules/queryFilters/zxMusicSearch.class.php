<?php

class zxMusicSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'zxMusic';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return ['internalTitle', 'description'];
    }
}