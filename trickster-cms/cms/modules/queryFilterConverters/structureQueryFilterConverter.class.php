<?php

class structureQueryFilterConverter extends QueryFilterConverter
{
    protected string $table = 'structure_elements';

    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table($this->getTable())->select('id');
        return $query;
    }

    protected function getTable(): string
    {
        return 'structure_elements';
    }
}