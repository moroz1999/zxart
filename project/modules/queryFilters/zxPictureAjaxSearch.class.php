<?php

class zxPictureAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName(): string
    {
        return 'zxPicture';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}