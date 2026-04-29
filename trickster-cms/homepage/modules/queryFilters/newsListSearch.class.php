<?php

class newsListSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'newsList';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return [];
    }
}