<?php

class zxReleaseAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'zxRelease';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}