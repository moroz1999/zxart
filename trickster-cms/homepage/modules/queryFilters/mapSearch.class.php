<?php

class mapSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'map';
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