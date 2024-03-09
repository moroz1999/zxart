<?php

class tagQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxMusic') {
            $query = $this->generateParentQuery($sourceData, 'module_tag', 'tagLink', true);
        } elseif ($sourceType == 'zxPicture') {
            $query = $this->generateParentQuery($sourceData, 'module_tag', 'tagLink', true);
        } else {
            $query = $this->getService('db')->table('module_tag')->select($this->getFields())->distinct();
        }
        return $query;
    }
}
