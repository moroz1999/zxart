<?php

class detailedSearchQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_detailedsearch')->select($this->getFields());
        return $query;
    }
}