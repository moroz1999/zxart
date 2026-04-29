<?php

class galleryAjaxSearchQueryFilter extends ajaxSearchQueryFilter
{
    protected function getTypeName()
    {
        return 'gallery';
    }

    protected function getTitleFieldNames()
    {
        return ['title'];
    }
}