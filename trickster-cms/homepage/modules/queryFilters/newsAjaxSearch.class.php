<?php

class newsAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'news';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}