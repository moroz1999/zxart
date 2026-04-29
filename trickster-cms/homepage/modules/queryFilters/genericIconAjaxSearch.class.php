<?php

class genericIconAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'genericIcon';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}