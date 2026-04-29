<?php

class folderSearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'folder';
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