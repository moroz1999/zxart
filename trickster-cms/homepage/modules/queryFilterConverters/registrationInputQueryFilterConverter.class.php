<?php

class registrationInputQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_form_field')->select('id')->distinct();
        return $query;
    }
}