<?php

use Illuminate\Database\Connection;

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
    protected $pager;
    protected int $prodsAmount;

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
            $this->getSubCategoriesTreeIds($subcategoriesIds);
            $subcategoriesIds = array_unique($subcategoriesIds);

            $url = $this->getFilterUrl();
            $amountOnPage = 100;
            $order = ['title' => 'asc'];
            $controller = $this->getService('controller');
            $currentPage = $controller->getParameter('page');
            if (!$currentPage) {
                $currentPage = 1;
            }
            $filters = ['zxProdCategory' => $subcategoriesIds];
            if ($letter = $controller->getParameter('letter')) {
                if (in_array($letter, self::$letters)) {
                    $filters['zxProdFirstLetter'] = $letter;
                }
            }
            if ($year = (int)$controller->getParameter('year')) {
                if ($year > 0) {
                    $filters['zxProdYear'] = $year;
                }
            }

            /**
             * @var ApiQueriesManager $apiQueriesManager
             */
            $apiQueriesManager = $this->getService('ApiQueriesManager');
            $apiQuery = $apiQueriesManager->getQuery()->setExportType('zxProd')->setFiltrationParameters($filters)
                ->setLimit($amountOnPage)->setStart($amountOnPage * ($currentPage - 1))->setOrder($order);
            if ($result = $apiQuery->getQueryResult()) {
                $this->prods = $result['zxProd'];
                $this->prodsAmount = $result['totalAmount'];
                $this->pager = new pager($url, $result['totalAmount'], $amountOnPage, $currentPage);
            }
        }

        return $this->prods;
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

    protected $prodsListBaseOptimizedQuery;

    protected function getProdsListBaseOptimizedQuery()
    {
        if ($this->prodsListBaseOptimizedQuery === null) {
            /**
             * @var Connection $db
             */
            $db = $this->getService('db');

            $prodsListBaseQuery = $this->getProdsListBaseQuery();
            $db->insert($db->raw("DROP TABLE IF EXISTS engine_baseprods"));
            $db->insert(
                $db->raw("CREATE TEMPORARY TABLE engine_baseprods " . $prodsListBaseQuery->toSql()),
                $prodsListBaseQuery->getBindings()
            );
            $this->prodsListBaseOptimizedQuery = $db->table('baseprods')->select('id');
        }
        return $this->prodsListBaseOptimizedQuery;
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

    public function getPager()
    {
        if ($this->pager === null) {
            $this->getProds();
        }
        return $this->pager;
    }

    protected function getFilterUrl($ignore = false)
    {
        $controller = $this->getService('controller');

        $url = $this->getUrl();
        if (($ignore != 'letter') && $letter = $controller->getParameter('letter')) {
            if (in_array($letter, self::$letters)) {
                $url .= 'letter:' . $letter . '/';
            }
        }
        if (($ignore != 'year') && $year = $controller->getParameter('year')) {
            $url .= 'year:' . (int)$year . '/';
        }
        return $url;
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

    public function getYearsSelectorInfo()
    {
        if ($this->yearsSelectorInfo === null) {
            $this->yearsSelectorInfo = [];
            $url = $this->getFilterUrl('year');
            /**
             * @var Connection $db
             */
            $db = $this->getService('db');
            $query = $db->table(ZXPRODS_TABLE)->select('year')->groupBy('year');
            if ($records = $query->get()) {
                foreach ($records as $record) {
                    if ($record['year']) {
                        $this->yearsSelectorInfo[] = [
                            'url' => $url . 'year:' . $record['year'] . '/',
                            'title' => $record['year'],
                        ];
                    }
                }
            }
        }
        return $this->yearsSelectorInfo;
    }

    public function getLettersSelectorInfo()
    {
        if ($this->lettersSelectorInfo === null) {
            $this->lettersSelectorInfo = [];
            $url = $this->getFilterUrl('letter');
            foreach (self::$letters as $letter) {
                $this->lettersSelectorInfo[] = [
                    'url' => $url . 'letter:' . $letter . '/',
                    'title' => $letter,
                ];
            }
        }

        return $this->lettersSelectorInfo;
    }
}