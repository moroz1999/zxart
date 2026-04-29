<?php

class formFieldAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'formField';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}