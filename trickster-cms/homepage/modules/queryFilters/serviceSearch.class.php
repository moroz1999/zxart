<?php

class serviceSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'service';
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