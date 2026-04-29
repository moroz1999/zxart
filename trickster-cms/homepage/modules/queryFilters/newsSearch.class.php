<?php

class newsSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'news';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return ['content', 'introduction'];
    }
}