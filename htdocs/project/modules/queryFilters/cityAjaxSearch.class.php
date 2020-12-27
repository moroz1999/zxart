<?php

class cityAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'city';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}