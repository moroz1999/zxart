<?php

class articleAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'article';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}