<?php

class zxMusicAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'zxMusic';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'internalTitle'];
    }
}