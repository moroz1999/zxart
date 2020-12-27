<?php

class zxMusicAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'zxMusic';
    }

    protected function getTitleFieldNames()
    {
        return ['title', 'internalTitle'];
    }
}