<?php

class groupAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'group';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}