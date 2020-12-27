<?php

class zxPictureSearchQueryFilter extends searchQueryFilter
{

    protected function getTypeName()
    {
        return 'zxPicture';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }

    protected function getContentFieldNames()
    {
        return ['description'];
    }
}