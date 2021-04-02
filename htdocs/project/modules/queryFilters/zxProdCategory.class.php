<?php

class zxProdCategoryQueryFilter extends QueryFilter
{

    public function getRequiredType()
    {
        return 'zxProd';
    }

    public function getFilteredIdList($argument, $query)
    {
        $argument = (array)$argument;
        $structureManager = $this->getService('structureManager');
        $ids = [];
        foreach ($argument as $categoryId) {
            /**
             * @var zxProdCategoryElement $category
             */
            if ($category = $structureManager->getElementById($categoryId)) {
                $ids[] = $categoryId;
                $ids = array_merge($ids, $category->gatherSubCategoriesTreeIds());
            }
        }
        $query->whereIn(
            'module_zxprod.id',
            function ($subQuery) use ($ids) {
                $subQuery->from('structure_links')->select('childStructureId')->where(
                    'type',
                    '=',
                    'zxProdCategory'
                )->whereIn('parentStructureId', $ids);
            }
        );

        return $query;
    }
}