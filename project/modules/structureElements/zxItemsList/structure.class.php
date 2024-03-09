<?php

/**
 * @property string title
 * @property string searchFormParametersString
 * @property string items
 * @property string apiString
 * @property string layout
 * @property bool requiresUser
 */
class zxItemsListElement extends structureElement implements JsonDataProvider
{
    use CacheOperatingElement;
    use JsonDataProviderElement;

    public $dataResourceName = 'module_zxitemslist';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $itemsList;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['type'] = 'text';
        $moduleStructure['items'] = 'text';
        $moduleStructure['apiString'] = 'text';
        $moduleStructure['searchFormParametersString'] = 'text';
        $moduleStructure['layout'] = 'text';
        $moduleStructure['buttonTitle'] = 'text';
        $moduleStructure['requiresUser'] = 'checkbox';
    }

    public function getProdsInfo(): array
    {
        $prodsInfo = [];
        foreach ($this->getItemsList() as $prod) {
            $prodsInfo[] = $prod->getElementData('list');
        }
        return $prodsInfo;
    }

    public function getProdsAmount(): int
    {
        return count($this->getItemsList());
    }

    public function getItemsList()
    {
        if ($this->itemsList === null) {
            $cache = $this->getElementsListCache('il', 60 * 60 * 7);
            if (($this->itemsList = $cache->load()) === false) {
                $structureType = '';
                if ((!$this->items) || $this->items == 'graphics') {
                    $structureType = 'zxPicture';
                } elseif ($this->items == 'music') {
                    $structureType = 'zxMusic';
                } elseif ($this->items == 'zxProd') {
                    $structureType = 'zxProd';
                } elseif ($this->items == 'zxRelease') {
                    $structureType = 'zxRelease';
                }
                /**
                 * @var ApiQueriesManager $apiQueriesManager
                 */
                $apiQueriesManager = $this->getService('ApiQueriesManager');
                if ($apiQuery = $apiQueriesManager->getQueryFromString($this->apiString)) {
                    if ($result = $apiQuery->getQueryResult()) {
                        $this->itemsList = $result[$structureType];
                    }
                }
                if (!$this->requiresUser) {
                    $cache->save($this->itemsList);
                }
            }
        }
        return $this->itemsList;
    }

    public function getCatalogueUrl(): string|null
    {
        if ($this->searchFormParametersString) {
            if ($this->items === 'zxProd' || $this->items === 'zxRelease') {
                $languagesManager = $this->getService('LanguagesManager');
                $currentLanguageId = $languagesManager->getCurrentLanguageId();

                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                if ($catalogueElements = $structureManager->getElementsByType('zxprodcategoriescatalogue', $currentLanguageId)) {
                    $catalogueElement = reset($catalogueElements);
                    return $catalogueElement->getFirstParentElement()->getUrl() . $this->searchFormParametersString;
                }
            }
        }
        return null;
    }
}


