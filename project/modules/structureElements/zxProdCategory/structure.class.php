<?php

class zxProdCategoryElement extends structureElement implements
    MetadataProviderInterface,
    ZxProdsProvider,
    JsonDataProvider
{
    use MetadataProviderTrait;
    use ImportedItemTrait;
    use ZxProdCategoriesTreeProvider;
    use ZxProdsList;
    use CacheOperatingElement;
    use JsonDataProviderElement {
        JsonDataProviderElement::getElementData as public traitGetElementData;
    }

    public $dataResourceName = 'module_zxprodcategory';
    public $allowedTypes = ['zxProdCategory', 'soft'];
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $viewName = 'short';

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['metaTitle'] = 'text';
        $moduleStructure['metaDescription'] = 'text';
        $moduleStructure['canonicalUrl'] = 'url';
        $moduleStructure['metaDenyIndex'] = 'checkbox';

        $moduleStructure['h1'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
        $multiLanguageFields[] = 'title';
        $multiLanguageFields[] = 'metaTitle';
        $multiLanguageFields[] = 'metaDescription';
        $multiLanguageFields[] = 'h1';
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'showForm', 'showSeoForm', 'showPositions', 'showPrivileges'}
     */
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

    public function getParentCategoryId(): ?int
    {
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');
        if ($parentElement = $structureManager->getElementsFirstParent($this->id, false, 'structure')) {
            if ($parentElement->structureType === 'zxProdCategory') {
                return $parentElement->id;
            }
        }

        return null;
    }


    public function getParentCategory(): ?zxProdCategoryElement
    {
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');
        if ($parentElement = $structureManager->getElementsFirstParent($this->id, false, 'structure')) {
            if ($parentElement->structureType === 'zxProdCategory') {
                return $parentElement;
            }
        }
        return null;
    }

    public function getRootCategory(): ?zxProdCategoryElement
    {
        $rootCategory = $this;
        while ($parentElement = $rootCategory->getParentCategory()) {
            $rootCategory = $parentElement;
        }
        return $rootCategory;
    }

    public function getSubCategoriesTreeIds(array &$ids = []): void
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


    public function getElementData(?string $preset = null): ?array
    {
        $selectorsUnChanged = $this->hasDefaultSelectors() && $this->hasDefaultSorting();
        if ($selectorsUnChanged) {
            /**
             * @var languagesManager $languagesManager
             */
            $languagesManager = $this->getService('languagesManager');

            $page = $this->getCurrentPage();
            $ttl = $page === 1 ? 60 * 60 * 24 : 60 * 60 * 2;
            $key = $this->id . $page . $languagesManager->getCurrentLanguageId();
            if ($data = $this->getCacheKey($key)) {
                return $data;
            }
        }

        if ($data = $this->traitGetElementData($preset)) {
            if ($selectorsUnChanged) {
                $this->setCacheKey($key, $data, $ttl);
            }
            return $data;
        }

        return null;
    }

    public function getCanonicalUrl()
    {
        $url = $this->URL;
        $page = $this->getCurrentPage();

        if ($page > 1) {
            $url .= "page:{$page}/";
        }
        return $url;
    }

    public function getMetaTitle()
    {
        $parentCategory = $this->getParentCategory();
        if ($parentCategory !== null) {
            $metaTitle = $parentCategory->getMetaTitle() . ' / ';
        } else {
            $metaTitle = '';
        }

        if ($this->metaTitle) {
            $metaTitle .= $this->metaTitle;
        } else {
            $metaTitle .= $this->title;
        }

        if ($this->final) {
            $metaTitle .= ' ZX Spectrum';
            $page = $this->getCurrentPage();

            if ($page > 1) {
                $translationsManager = $this->getService('translationsManager');
                $metaTitle .= " (" . $translationsManager->getTranslationByName('zxprodcategory.page') . " {$page})";
            }
        }
        return $metaTitle;
    }
}