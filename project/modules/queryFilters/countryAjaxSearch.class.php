<?php

class countryAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'country';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}