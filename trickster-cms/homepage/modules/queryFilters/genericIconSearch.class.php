<?php

class genericIconSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'genericIcon';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return false;
    }
}