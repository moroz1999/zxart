<?php

class zxProdCategoryQueryFilterConverter extends QueryFilterConverter
{
    use LinkedQueryFilterTrait;

    public function convert($sourceData, $sourceType)
    {
        if ($sourceType == 'zxProd') {
            $query = $this->generateParentQuery($sourceData, 'module_zxprodcategory', 'authorPicture', true);
        } else {
            $query = $this->getService('db')->table('module_zxprodcategory')->select($this->getFields())->distinct();
        }
        return $query;
    }
}