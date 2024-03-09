<?php

class zxPictureSearchQueryFilter extends searchQueryFilter
{

    protected function getTypeName(): string
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