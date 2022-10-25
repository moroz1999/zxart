<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

const ZXPRODS_TABLE = 'module_zxprod';

trait ZxProdsList
{
    use HardwareProviderTrait;
    use ReleaseFormatsProvider;

    /**
     * @var zxProdCategoryElement[]
     */
    protected $categories;
    /**
     * @var zxProdElement[]
     */
    protected array $prods;
    protected int $prodsAmount;

    protected ApiQuery $apiQuery;
    protected ?Builder $filteredQuery;
    protected ?Builder $baseQuery;

    protected array $tagsSelector;
    protected array $categoriesSelector;
    protected array $yearsSelector;
    protected array $legalStatusesSelector;
    protected array $hardwareSelector;
    protected array $countriesSelector;
    protected array $formatsSelector;
    protected array $languagesSelector;
    protected array $lettersSelector;
    protected array $sortingSelector;

    abstract public function getProdsListBaseQuery();

    private $type = 'zxProd';

    public function getProdsInfo(): array
    {
        $prodsInfo = [];
        foreach ($this->getProds() as $prod) {
            $prodsInfo[] = $prod->getElementData('list');
        }
        return $prodsInfo;
    }

    /**
     * @return zxProdElement[]
     */
    public function getProds(): array
    {
        if (!isset($this->prods)) {
            $this->prods = [];
            if ($apiQuery = $this->getApiQuery()) {
                if ($this->getReleasesValue()) {
                    $apiQuery->setExportType('zxRelease');
                    $apiQuery->setResultTypes(['zxRelease']);
                    if ($result = $apiQuery->getQueryResult()) {
                        $this->prods = $result['zxRelease'];
                        $this->prodsAmount = $result['totalAmount'];
                    }
                } else {
                    if ($result = $apiQuery->getQueryResult()) {
                        $this->prods = $result[$this->type];
                        $this->prodsAmount = $result['totalAmount'];
                    }
                }

            }
        }
        return $this->prods;
    }


    public function getApiQuery()
    {
        if (!isset($this->apiQuery)) {
            $subcategoriesIds = [];
            $this->getSubCategoriesTreeIds($subcategoriesIds);
            $subcategoriesIds = array_unique($subcategoriesIds);
            $controller = $this->getService('controller');

            $filters = ['zxProdCategory' => $subcategoriesIds];
            /**
             * @var ApiQueriesManager $apiQueriesManager
             */
            $apiQueriesManager = $this->getService('ApiQueriesManager');
            $apiQuery = $apiQueriesManager->getQuery()->setExportType($this->type)->setFiltrationParameters($filters);
            $this->baseQuery = $apiQuery->getExportFilteredQuery();
            if ($letter = $controller->getParameter('letter')) {
                $filters['zxProdFirstLetter'] = $letter;
            }
            if ($values = $this->getSelectorValue('years')) {
                $filters['zxProdYear'] = $values;
            }
            if ($values = $this->getSelectorValue('statuses')) {
                $filters['zxProdStatus'] = $values;
            }
            if ($values = $this->getSelectorValue('tags')) {
                $filters['zxProdTagsInclude'] = $values;
            }
            if ($values = $this->getSelectorValue('countries')) {
                $filters['zxProdCountryId'] = $values;
            }
            if ($values = $this->getSelectorValue('hw')) {
                $filters['zxReleaseHardware'] = $values;
            }
            if ($values = $this->getSelectorValue('formats')) {
                $filters['zxReleaseFormat'] = $values;
            }
            if ($values = $this->getSelectorValue('languages')) {
                $filters['zxReleaseLanguage'] = $values;
            }
            $this->apiQuery = $apiQueriesManager->getQuery()->setExportType($this->type)->setFiltrationParameters($filters);
            $this->filteredQuery = clone($this->apiQuery->getExportFilteredQuery());
            $elementsOnPage = (int)$controller->getParameter('elementsOnPage');
            if (!$elementsOnPage) {
                $elementsOnPage = 100;
            }
            if ($value = $this->getSelectorValue('sorting')) {
                $sort = [$value[0] => $value[1]];
            }
            $currentPage = (int)$controller->getParameter('page');
            if (!$currentPage) {
                $currentPage = 1;
            }
            $this->apiQuery->setLimit($elementsOnPage)
                ->setStart($elementsOnPage * ($currentPage - 1))
                ->setOrder($sort);
        }
        return $this->apiQuery;
    }

    public function getProdsAmount()
    {
        if (!isset($this->prodsAmount)) {
            $this->getProds();
        }
        return $this->prodsAmount;
    }

    protected function getProdsQuery()
    {
        /**
         * @var Connection $db
         */
        $db = $this->getService('db');

        //basic query to get all non-hidden prods available in stock
        $query = $db->table(ZXPRODS_TABLE);
        $query->select(
            [
                ZXPRODS_TABLE . '.id',
                ZXPRODS_TABLE . '.title',
            ]
        );

        return $query;
    }

    /**
     * @return zxProdCategoryElement[]
     */
    public function getCategories()
    {
        if ($this->categories === null) {
            $structureManager = $this->getService('structureManager');
            $this->categories = $structureManager->getElementsChildren($this->id, 'container', 'structure');
        }

        return $this->categories;
    }

    /**
     * @return int[]
     */
    public function getCategoriesIds()
    {
        $result = [];
        foreach ($this->getCategories() as $category) {
            $result[] = $category->id;
        }
        return $result;
    }

    private $yearsSelectorInfo;

    public function getYearsSelectorInfo()
    {
        if (!isset($this->yearsSelectorInfo)) {
            if ($yearsSelector = $this->getYearsSelector()) {
                $this->yearsSelectorInfo = [];
                $controller = $this->getService('controller');

                if ($years = $yearsSelector[0]['values']) {
                    foreach ($years as $letter) {
                        $this->yearsSelectorInfo[] = [
                            'url' => $controller->pathURL . 'year:' . $letter['value'],
                            'title' => $letter['title'],
                        ];
                    }
                }
            }

        }
        return $this->yearsSelectorInfo;
    }

    public function getYearsSelector(): array
    {
        if (!isset($this->yearsSelector)) {
            $this->yearsSelector = [];
            $selectorValues = [];
            if ($query = $this->getSelectorQuery('years')) {
                $values = $this->getSelectorValue('years');
                if ($values[0] === 'this') {
                    $values = [date('Y'), date('Y') - 1];
                }
                $years = $query
                    ->distinct()
                    ->orderBy('year', 'asc')
                    ->where('year', '!=', 0)
                    ->pluck('year');

                foreach ($years as $year) {
                    $selectorValues[] = [
                        'value' => $year,
                        'title' => $year,
                        'selected' => $values && in_array($year, $values),
                    ];
                }
            }
            if ($selectorValues) {
                $this->yearsSelector[] = [
                    'title' => '',
                    'values' => $selectorValues
                ];
            }
        }
        return $this->yearsSelector;
    }

    private function getCategoriesCatalogue()
    {
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');

        $id = $this->id;
        while (($parentElement = $structureManager->getElementsFirstParent($id)) && ($parentElement->structureType === 'zxProdCategory')) {
            $id = $parentElement->id;
        }
        $elements = $parentElement->getChildrenList();
        return reset($elements);
    }

    public function getCategoriesSelector(): array
    {
        if (!isset($this->categoriesSelector)) {
            $this->categoriesSelector = [];
            $selectorValues = [];
            $catalogue = $this->getCategoriesCatalogue();
            $categories = $catalogue->getCategories();
            foreach ($categories as $category) {
                $this->getRecursiveCategorySelectorValues($category, $selectorValues);
            }
            if ($selectorValues) {
                $this->categoriesSelector = $selectorValues;
            }
        }
        return $this->categoriesSelector;
    }

    private function getRecursiveCategorySelectorValues(zxProdCategoryElement $category, &$selectorValues, &$selected = false)
    {
        $selected = $category->id === $this->id;
        $data = [
            'name' => html_entity_decode($category->title, ENT_QUOTES),
            'id' => $category->id,
            'url' => $category->getUrl(),
        ];
        if ($categories = $category->getCategories()) {
            $data['children'] = [];
            foreach ($categories as $subCategory) {
                $childSelected = false;
                $this->getRecursiveCategorySelectorValues($subCategory, $data['children'], $childSelected);
                if ($childSelected) {
                    $selected = true;
                }
            }
        }
        $data['selected'] = $selected;
        $selectorValues[] = $data;
    }

    public function getLegalStatusesSelector(): array
    {
        if (!isset($this->legalStatusesSelector)) {
            $this->legalStatusesSelector = [];
            $selectorValues = [];
            if ($query = $this->getSelectorQuery('statuses')) {
                /**
                 * @var translationsManager $translationsManager
                 */
                $translationsManager = $this->getService('translationsManager');

                $values = $this->getSelectorValue('statuses');
                $statuses = $query
                    ->where('legalStatus', '!=', '')
                    ->distinct()
                    ->pluck('legalStatus');

                foreach ($statuses as $status) {
                    $selectorValues[] = [
                        'value' => $status,
                        'title' => $translationsManager->getTranslationByName("legalstatus.{$status}"),
                        'selected' => $values && in_array($status, $values),
                    ];
                }
            }
            if ($selectorValues) {
                $this->legalStatusesSelector[] = [
                    'title' => '',
                    'values' => $selectorValues
                ];
            }
        }
        return $this->legalStatusesSelector;
    }

    public function getHardwareSelector(): array
    {
        if (!isset($this->hardwareSelector)) {
            $this->hardwareSelector = [];
            if ($query = $this->getSelectorQuery('hw')) {
                $values = $this->getSelectorValue('hw');
                $db = $this->getService('db');
                /**
                 * @var translationsManager $translationsManager
                 */
                $translationsManager = $this->getService('translationsManager');

                /**
                 * @var QueryFiltersManager $queryFiltersManager
                 */
                $queryFiltersManager = $this->getService('QueryFiltersManager');
                $query = $queryFiltersManager->convertTypeData($query, 'zxRelease', 'zxProd', [])->select('id');
                $hwItems = $db->table('module_zxrelease_hw_required')
                    ->whereIn('elementId', $query)
                    ->distinct()
                    ->pluck('value');
                foreach ($this->getHardwareList() as $groupName => $groupValues) {
                    if ($intersected = array_intersect($groupValues, $hwItems)) {
                        $group = [
                            'title' => $translationsManager->getTranslationByName("hardware.group_{$groupName}"),
                            'values' => []
                        ];
                        foreach ($intersected as $hwItem) {
                            $group['values'][] = [
                                'value' => $hwItem,
                                'title' => $translationsManager->getTranslationByName('hardware.item_' . $hwItem),
                                'selected' => $values && in_array($hwItem, $values),
                            ];
                        }
                        $this->hardwareSelector[] = $group;
                    }
                }

            }
        }
        return $this->hardwareSelector;
    }

    public function getCountriesSelector(): array
    {
        if (!isset($this->countriesSelector)) {
            $this->countriesSelector = [];
            if ($query = $this->getSelectorQuery('countries')) {
                $query->select(['id']);
                $values = $this->getSelectorValue('countries');
                $db = $this->getService('db');

                $languageId = $this->getService('LanguagesManager')->getCurrentLanguageId();
                $countries = $db->table('module_country')
                    ->whereIn('id', function ($countriesQuery) use ($query) {
                        $countriesQuery->from('module_group')->select('country')->whereIn('id', function ($producersQuery) use ($query) {
                            $producersQuery->from('structure_links')->whereIn('childStructureId', $query)->where('type', '=', 'zxProdGroups')->select('parentStructureId');
                        });
                    })
                    ->select('title', 'id')
                    ->orderBy('title', 'asc')
                    ->where('languageId', '=', $languageId)
                    ->get();
                $group = [
                    'title' => '',
                    'values' => []
                ];
                foreach ($countries as $country) {
                    $group['values'][] = [
                        'value' => $country['id'],
                        'title' => $country['title'],
                        'selected' => $values && in_array($country['id'], $values),
                    ];
                }
                $this->countriesSelector[] = $group;

            }
        }
        return $this->countriesSelector;
    }

    public function getFormatsSelector(): array
    {
        if (!isset($this->formatsSelector)) {
            $this->formatsSelector = [];
            if ($query = $this->getSelectorQuery('formats')) {
                $values = $this->getSelectorValue('formats');
                $db = $this->getService('db');
                /**
                 * @var translationsManager $translationsManager
                 */
                $translationsManager = $this->getService('translationsManager');

                /**
                 * @var QueryFiltersManager $queryFiltersManager
                 */
                $queryFiltersManager = $this->getService('QueryFiltersManager');
                $query = $queryFiltersManager->convertTypeData($query, 'zxRelease', 'zxProd', [])->select('id');
                $hwItems = $db->table('module_zxrelease_format')
                    ->whereIn('elementId', $query)
                    ->distinct()
                    ->pluck('value');
                foreach ($this->getGroupedReleaseFormats() as $groupName => $groupValues) {
                    if ($intersected = array_intersect($groupValues, $hwItems)) {
                        $group = [
                            'title' => $translationsManager->getTranslationByName("formats.group_{$groupName}"),
                            'values' => []
                        ];
                        foreach ($intersected as $format) {
                            $group['values'][] = [
                                'value' => $format,
                                'title' => $translationsManager->getTranslationByName("zxRelease.filetype_{$format}"),
                                'selected' => $values && in_array($format, $values),
                            ];
                        }
                        $this->formatsSelector[] = $group;
                    }
                }

            }
        }
        return $this->formatsSelector;
    }

    public function getLanguagesSelector(): array
    {
        if (!isset($this->languagesSelector)) {
            $this->languagesSelector = [];
            if ($query = $this->getSelectorQuery('languages')) {
                $values = $this->getSelectorValue('languages');
                $db = $this->getService('db');
                /**
                 * @var translationsManager $translationsManager
                 */
                $translationsManager = $this->getService('translationsManager');

                /**
                 * @var QueryFiltersManager $queryFiltersManager
                 */
                $queryFiltersManager = $this->getService('QueryFiltersManager');
                $prodQuery = clone($query);
                $query = $queryFiltersManager->convertTypeData($query, 'zxRelease', 'zxProd', [])->select('id');
                $languages = $db->table('zxitem_language')
                    ->whereIn('elementId', $query)
                    ->orWhereIn('elementId', $prodQuery->select('id'))
                    ->distinct()
                    ->pluck('value');
                $group = [
                    'title' => "",
                    'values' => []
                ];
                $order = [];
                foreach ($languages as $language) {
                    $group['values'][] = [
                        'value' => $language,
                        'title' => $translationsManager->getTranslationByName("language.item_{$language}"),
                        'selected' => $values && in_array($language, $values),
                    ];
                    $order[] = $translationsManager->getTranslationByName("language.item_{$language}");
                }
                array_multisort($order, SORT_ASC, $group['values']);
                $this->languagesSelector[] = $group;
            }
        }
        return $this->languagesSelector;
    }

    public function getTagsSelector(): array
    {
        if (!isset($this->tagsSelector)) {
            $this->tagsSelector = [];
            if ($values = $this->getSelectorValue('tags')) {
                $structureManager = $this->getService('structureManager');
                foreach ($values as $id) {
                    if ($tagElement = $structureManager->getElementById($id)) {
                        $this->tagsSelector[] = $tagElement->getElementData();
                    }
                }
            }
        }
        return $this->tagsSelector;
    }

    public function getSortingSelector(): array
    {
        if (!isset($this->sortingSelector)) {
            if ($this->getReleasesValue()) {
                $sortTypes = [
                    'title,asc',
                    'title,desc',
                    'year,asc',
                    'year,desc',
                    'date,asc',
                    'date,desc',
                ];

            } else {
                $sortTypes = [
                    'votes,asc',
                    'votes,desc',
                    'title,asc',
                    'title,desc',
                    'year,asc',
                    'year,desc',
                    'date,asc',
                    'date,desc',
                ];
            }

            $selectorValues = [];
            $values = $this->getSelectorValue('sorting');
            $value = implode(',', $values);
            $this->sortingSelector = [];
            foreach ($sortTypes as $sortType) {
                $selectorValues[] = [
                    'value' => $sortType,
                    'title' => $sortType,
                    'selected' => $sortType === $value,
                ];
            }
            if ($selectorValues) {
                $this->sortingSelector[] = [
                    'title' => '',
                    'values' => $selectorValues
                ];
            }
        }
        return $this->sortingSelector;
    }

    private $lettersSelectorInfo;

    public function getLettersSelectorInfo()
    {
        if (!isset($this->lettersSelectorInfo)) {
            if ($lettersSelector = $this->getLettersSelector()) {
                $this->lettersSelectorInfo = [];
                $controller = $this->getService('controller');

                if ($letters = $lettersSelector[0]['values']) {
                    foreach ($letters as $letter) {
                        $this->lettersSelectorInfo[] = [
                            'url' => $controller->pathURL . 'letter:' . $letter['value'],
                            'title' => $letter['title'],
                        ];
                    }
                }
            }

        }
        return $this->lettersSelectorInfo;
    }

    public function getLettersSelector(): array
    {
        if (!isset($this->lettersSelector)) {
            $this->lettersSelector = [];
            $selectorValues = [];
            if ($query = $this->getSelectorQuery('letter')) {
                $value = $this->getSelectorValue('letter');
                $letters = $query
                    ->selectRaw("LEFT(title, 1) AS letter")
                    ->orderBy('letter', 'asc')
                    ->groupBy('letter')
                    ->pluck('letter');

                $numericExists = false;
                foreach ($letters as $letter) {
                    if (preg_match('/[a-zA-Z]/', $letter)) {
                        $selected = $value ? in_array($letter, $value) : false;
                        $selectorValues[] = [
                            'value' => $letter,
                            'title' => mb_strtoupper($letter),
                            'selected' => $selected
                        ];
                    } else {
                        $numericExists = true;
                    }
                }
                if ($numericExists) {
                    $selected = $value ? in_array('0-9', $value) : false;
                    array_unshift($selectorValues, [
                        'value' => '0-9',
                        'title' => '0-9',
                        'selected' => $selected
                    ]);
                }
            }
            if ($selectorValues) {
                $this->lettersSelector[] = [
                    'title' => '',
                    'values' => $selectorValues
                ];
            }
        }
        return $this->lettersSelector;
    }

    private function getSelectorQuery(string $selectorName)
    {
        $values = $this->getSelectorValue($selectorName);
        if ($values) {
            $query = clone($this->getBaseQuery());
        } else {
            $query = clone($this->getFilteredQuery());
        }
        return $query;
    }

    private function getSelectorValue(string $name): ?array
    {
        if (!isset($this->selectorValues)) {
            $this->selectorValues = [
                'sorting' => ['votes', 'desc'],
                'countries' => null,
                'languages' => null,
                'formats' => null,
                'hw' => null,
                'tags' => null,
                'years' => null,
                'statuses' => null,
                'letter' => null,
            ];
            foreach ($this->selectorValues as $selectorName => $selectorValue) {
                if ($parameter = controller::getInstance()->getParameter($selectorName)) {
                    $values = explode(',', $parameter);
                    $this->selectorValues[$selectorName] = [];
                    foreach ($values as $value) {
                        $this->selectorValues[$selectorName][] = trim($value);
                    }
                }
            }
        }

        return $this->selectorValues[$name];
    }

    private function getFilteredQuery(): ?Builder
    {
        if (!isset($this->filteredQuery)) {
            $this->getApiQuery();
        }
        return $this->filteredQuery;
    }

    private function getBaseQuery(): ?Builder
    {
        if (!isset($this->baseQuery)) {
            $this->getApiQuery();
        }
        return $this->baseQuery;
    }

    private function getReleasesValue(): bool
    {
        $controller = $this->getService('controller');
        return $controller->getParameter('releases') === '1';
    }

    public function getUrl($action = null)
    {
        if ($this->final) {
            $controller = $this->getService('controller');
            return parent::getUrl($action) . $controller->getParametersString();
        }
        return parent::getUrl($action);
    }

    public function getSelectorValues(): array
    {
        $controller = $this->getService('controller');
        return [
            'letter' => $controller->getParameter('letter') ?? '',
            'years' => $this->getSelectorValue('years') ?? [],
            'statuses' => $this->getSelectorValue('statuses') ?? [],
            'tags' => $this->getSelectorValue('tags') ?? [],
            'countries' => $this->getSelectorValue('countries') ?? [],
            'hw' => $this->getSelectorValue('hw') ?? [],
            'formats' => $this->getSelectorValue('formats') ?? [],
            'languages' => $this->getSelectorValue('languages') ?? [],
            'releases' => $this->getReleasesValue(),
        ];
    }

}