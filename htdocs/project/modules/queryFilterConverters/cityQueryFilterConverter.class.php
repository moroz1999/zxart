<?php

class cityQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_city')->select($this->getFields())->distinct();
        return $query;
    }
}