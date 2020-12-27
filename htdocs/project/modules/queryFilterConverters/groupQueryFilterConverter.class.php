<?php

class groupQueryFilterConverter extends QueryFilterConverter
{
    protected array $fields = [
        'id',
        'title',
    ];

    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_group')->select($this->fields);

        return $query;
    }
}
