<?php

class zxProdCategoryElement extends structureElement implements MetadataProviderInterface, ZxProdsProvider,
                                                                JsonDataProvider
{
    use MetadataProviderTrait;
    use ImportedItemTrait;
    use ZxProdCategoriesTreeProviderTrait;
    use ZxProdsList;
    use JsonDataProviderElement;

    public $dataResourceName = 'module_zxprodcategory';
    public $allowedTypes = ['zxProdCategory', 'soft'];
    public $defaultActionName = 'show';
    public $role = 'container';
    protected static $letters = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        '#',
    ];

    protected $viewName = 'short';

    protected $lettersSelectorInfo;
    protected $yearsSelectorInfo;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['metaTitle'] = 'text';
        $moduleStructure['metaDescription'] = 'text';
        $moduleStructure['canonicalUrl'] = 'url';
        $moduleStructure['metaDenyIndex'] = 'checkbox';

        $moduleStructure['metaDescriptionTemplate'] = 'text';
        $moduleStructure['metaTitleTemplate'] = 'text';

        $moduleStructure['metaH1Template'] = 'text';
        $moduleStructure['h1'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
        $multiLanguageFields[] = 'metaTitle';
        $multiLanguageFields[] = 'metaDescription';
        $multiLanguageFields[] = 'metaDescriptionTemplate';
        $multiLanguageFields[] = 'metaTitleTemplate';
        $multiLanguageFields[] = 'metaH1Template';
        $multiLanguageFields[] = 'h1';
    }

    protected function getTabsList()
    {
        return [
            'showForm',
            'showSeoForm',
            'showPositions',
            'showPrivileges',
        ];
    }


    /**
     * @return int[]
     */
    public function gatherSubCategoriesTreeIds()
    {
        $result = [];
        foreach ($this->getCategories() as $category) {
            $result[] = $category->id;
            $result = array_merge($result, $category->gatherSubCategoriesTreeIds());
        }
        return $result;
    }

    /**
     * @return int
     */
    public function getParentCategoryId()
    {
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');
        if ($parentElement = $structureManager->getElementsFirstParent($this->id, false, 'structure')) {
            if ($parentElement->structureType == 'zxProdCategory') {
                return $parentElement->id;
            }
        }


        return false;
    }

    public function getSubCategoriesTreeIds(&$ids = [])
    {
        $ids[] = $this->id;
        if ($subcategories = $this->getCategories()) {
            foreach ($subcategories as $category) {
                $category->getSubCategoriesTreeIds($ids);
            }
        }
    }

    public function getProdsListBaseQuery()
    {
        return $this->getProdsQuery();
    }
}