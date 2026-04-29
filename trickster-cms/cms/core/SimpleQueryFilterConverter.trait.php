<?php

trait SimpleQueryFilterConverterTrait
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table($this->getTable())->select('id')->distinct();
        return $query;
    }
}