<?php

class eventSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'event';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return ['description', 'introduction'];
    }
}