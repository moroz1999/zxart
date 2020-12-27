<?php

class zxPictureAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'zxPicture';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}