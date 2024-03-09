<?php

trait CategoryElementsSelectorProviderTrait
{
    private $categoriesInfo;
    protected $connectedCategoriesIds;
    protected $connectedCategories;

    public function getCategoriesSelectorInfo()
    {
        if ($this->categoriesInfo === null) {
            $this->categoriesInfo = [];
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            /**
             * @var LanguagesManager $languagesManager
             */
            $languagesManager = $this->getService('LanguagesManager');
            if ($connectedIds = $this->getConnectedCategoriesIds()) {
                $index = array_flip($connectedIds);
            } else {
                $index = [];
            }

            if ($categoriesElements = $structureManager->getElementsByType(
                'zxProdCategoriesCatalogue',
                $languagesManager->getCurrentLanguageId()
            )) {
                if ($categoriesElement = reset($categoriesElements)) {
                    foreach ($categoriesElement->getCategoriesTree() as $category) {
                        $this->categoriesInfo[] = [
                            'id' => $category->id,
                            'title' => $category->title,
                            'level' => $category->level,
                            'selected' => isset($index[$category->id]),
                        ];
                    }
                }
            }
        }
        return $this->categoriesInfo;
    }

    /**
     * @return zxProdCategoryElement[]
     */
    public function getConnectedCategories()
    {
        if ($this->connectedCategories === null) {
            $this->connectedCategories = [];
            /**
             * @var structureManager $structureManager
             */
            $structureManager = $this->getService('structureManager');
            if ($connectedIds = $this->getConnectedCategoriesIds()) {
                $this->connectedCategories = $structureManager->getElementsByIdList($connectedIds);
            }
        }
        return $this->connectedCategories;
    }

    abstract public function getConnectedCategoriesIds();
}