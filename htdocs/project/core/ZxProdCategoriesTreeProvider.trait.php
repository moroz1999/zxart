<?php

trait ZxProdCategoriesTreeProviderTrait
{
    public function getCategoriesTree(&$result = [])
    {
        foreach ($this->getCategories() as $category) {
            $result[] = $category;
            $category->getCategoriesTree($result);
        }
        return $result;
    }
}