<?php

class articleSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'article';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return ['content'];
    }
}