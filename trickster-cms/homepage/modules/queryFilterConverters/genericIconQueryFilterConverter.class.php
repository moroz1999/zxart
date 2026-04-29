<?php

class genericIconQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_generic_icon')->select('id')->distinct();
        return $query;
    }
}