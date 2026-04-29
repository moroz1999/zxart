<?php

class newsListAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'newsList';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}