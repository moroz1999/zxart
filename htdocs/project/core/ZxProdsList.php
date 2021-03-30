<?php

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

const ZXPRODS_TABLE = 'module_zxprod';

trait ZxProdsList
{
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

    abstract public function getProdsListBaseQuery();

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
                if ($result = $apiQuery->getQueryResult()) {
                    $this->prods = $result['zxProd'];
                    $this->prodsAmount = $result['totalAmount'];
                }
            }
        }

        return $this->prods;
    }

    public function getApiQuery()
    {
        if (!isset($this->apiQuery)) {
            $this->getSubCategoriesTreeIds($subcategoriesIds);
            $subcategoriesIds = array_unique($subcategoriesIds);
            $controller = $this->getService('controller');

            $filters = ['zxProdCategory' => $subcategoriesIds];
            /**
             * @var ApiQueriesManager $apiQueriesManager
             */
            $apiQueriesManager = $this->getService('ApiQueriesManager');
            $apiQuery = $apiQueriesManager->getQuery()->setExportType('zxProd')->setFiltrationParameters($filters);
            $this->baseQuery = $apiQuery->getExportFilteredQuery();

            if ($letter = $controller->getParameter('letter')) {
                if (in_array($letter, self::$letters)) {
                    $filters['zxProdFirstLetter'] = $letter;
                }
            }
            $filterYears = [];
            $yearFrom = (int)$controller->getParameter('yearFrom');
            $yearTo = (int)$controller->getParameter('yearTo');
            if ($yearFrom && $yearTo && $yearFrom < $yearTo) {
                for ($year = $yearFrom; $year <= $yearTo; $year++) {
                    $filterYears[] = $year;
                }
            } elseif ($yearFrom) {
                $filterYears[] = $yearFrom;
            } elseif ($yearTo) {
                $filterYears[] = $yearTo;
            }
            if ($filterYears) {
                $filters['zxProdYear'] = $filterYears;
            }
            $this->apiQuery = $apiQueriesManager->getQuery()->setExportType('zxProd')->setFiltrationParameters($filters);
            $this->filteredQuery = clone($this->apiQuery->getExportFilteredQuery());

            $elementsOnPage = (int)$controller->getParameter('elementsOnPage');
            if (!$elementsOnPage) {
                $elementsOnPage = 100;
            }
            $order = ['title' => 'asc'];
            $currentPage = (int)$controller->getParameter('page');
            if (!$currentPage) {
                $currentPage = 1;
            }
            $this->apiQuery->setLimit($elementsOnPage)
                ->setStart($elementsOnPage * ($currentPage - 1))
                ->setOrder($order);
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

    public function getYearsSelector(): array
    {
        if ($this->yearsSelector === null) {
            $this->yearsSelector = [];
            $values = $this->getSelectorValues('year');
            $values = explode(',', $values);
            if ($values) {
                $query = clone($this->getBaseQuery());
            } else {
                $query = clone($this->getFilteredQuery());
            }
            if ($query) {
                $years = $query
                    ->distinct()
                    ->orderBy('year', 'asc')
                    ->where('year', '!=', 0)
                    ->pluck('year');

                foreach ($years as $year) {
                    $this->yearsSelector[] = [
                        'value' => $year,
                        'title' => $year,
                        'selected' => in_array($year, $values),
                    ];
                }
            }
        }
        return $this->yearsSelector;
    }

    private function getSelectorValues(string $name): ?string
    {
        if ($value = controller::getInstance()->getParameter($name)) {
            return $value;
        }
        return null;
    }

    private function getFilteredQuery(): ?Builder
    {
        if (!$this->filteredQuery) {
            $this->getApiQuery();
        }
        return $this->filteredQuery;
    }

    private function getBaseQuery(): ?Builder
    {
        if (!$this->baseQuery) {
            $this->getApiQuery();
        }
        return $this->baseQuery;
    }

//    public function getLettersSelectorInfo()
//    {
//        if ($this->lettersSelectorInfo === null) {
//            $this->lettersSelectorInfo = [];
//            $url = $this->getFilterUrl('letter');
//            foreach (self::$letters as $letter) {
//                $this->lettersSelectorInfo[] = [
//                    'url' => $url . 'letter:' . $letter . '/',
//                    'title' => $letter,
//                ];
//            }
//        }
//
//        return $this->lettersSelectorInfo;
//    }
}