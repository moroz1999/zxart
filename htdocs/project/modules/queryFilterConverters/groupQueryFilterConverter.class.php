<?php

class groupQueryFilterConverter extends QueryFilterConverter
{
    protected function getFields(): array
    {
        return [
            'id',
            'title',
        ];
    }
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_group')->select($this->getFields());

        return $query;
    }
}
