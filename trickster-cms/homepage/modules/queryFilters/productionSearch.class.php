<?php

class productionSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'production';
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