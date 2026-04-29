<?php

class folderAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'folder';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}