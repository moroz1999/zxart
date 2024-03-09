<?php

class groupAliasQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_groupalias')->select($this->getFields());

        return $query;
    }
}
