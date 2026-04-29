<?php

class gallerySearchQueryFilter extends searchQueryFilter
{
    protected function getTypeName()
    {
        return 'gallery';
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