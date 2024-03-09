<?php

class cityAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'city';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}