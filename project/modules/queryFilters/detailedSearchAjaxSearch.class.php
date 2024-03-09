<?php

class detailedSearchAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'detailedSearch';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}