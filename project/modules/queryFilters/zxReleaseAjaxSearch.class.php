<?php

class zxReleaseAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'zxRelease';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}