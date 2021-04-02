<?php

class countryAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'country';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}