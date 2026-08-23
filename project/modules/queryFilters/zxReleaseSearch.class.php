<?php

class zxReleaseSearchQueryFilter extends searchQueryFilter
{

    protected function getTypeName()
    {
        return 'zxRelease';
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