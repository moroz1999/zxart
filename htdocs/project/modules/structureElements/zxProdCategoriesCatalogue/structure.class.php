<?php

class zxProdCategoriesCatalogueElement extends structureElement implements JsonDataProvider
{
    use ZxProdCategoriesTreeProvider;
    use ZxProdsList;
    use JsonDataProviderElement;

    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $allowedTypes = [''];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $replacementElements = [];
    protected $viewName = 'details';
    /**
     * @var zxProdCategoryElement[]
     */
    protected $categories;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    /**
     * @return zxProdCategoryElement[]
     */
    public function getCategories()
    {
        if ($this->categories === null) {
            $this->categories = [];
            if (($firstParent = $this->getFirstParentElement())) {
                $structureManager = $this->getService('structureManager');
                $this->categories = $structureManager->getElementsChildren(
                    $firstParent->id,
                    'container',
                    'softCatalogue'
                );
            }
        }

        return $this->categories;
    }

    public function getSubCategoriesTreeIds(&$ids)
    {
        foreach ($this->getCategories() as $category) {
            $category->getSubCategoriesTreeIds($ids);
        }
        return $ids;
    }

    public function getProdsListBaseQuery()
    {
        return $this->getProdsQuery();
    }
}


