<?php

class bannerCategoryQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_banner_category')->select('id');
        return $query;
    }
}