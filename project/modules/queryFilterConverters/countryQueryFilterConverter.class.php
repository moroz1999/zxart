<?php

class countryQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_country')->select($this->getFields())->distinct();
        return $query;
    }
}