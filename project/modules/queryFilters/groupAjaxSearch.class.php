<?php

class groupAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'group';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}