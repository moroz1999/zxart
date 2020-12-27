<?php

class countryAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getResourceName()
    {
        return 'generic';
    }

    protected function getTypeName()
    {
        return 'country';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}