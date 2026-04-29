<?php

class eventAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'event';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}