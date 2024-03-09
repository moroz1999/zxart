<?php

class pressArticleQueryFilterConverter extends QueryFilterConverter
{
    public function convert($sourceData, $sourceType)
    {
        $query = $this->getService('db')->table('module_pressarticle')->select($this->getFields())->distinct();
        return $query;
    }
}