<?php

class detailedSearchAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'detailedSearch';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}