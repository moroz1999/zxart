<?php

class zxProdSearchQueryFilter extends searchQueryFilter
{

    protected function getTypeName()
    {
        return 'zxProd';
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