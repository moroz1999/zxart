<?php

class zxProdCategoriesCatalogueElement extends structureElement
{
    use ZxProdCategoriesTreeProviderTrait;

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
}


