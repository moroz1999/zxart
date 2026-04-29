<?php

class userGroupQueryFilterConverter extends QueryFilterConverter
{
    protected string $table = 'module_user_group';

    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_user_group')->select('id');
        return $query;
    }
}