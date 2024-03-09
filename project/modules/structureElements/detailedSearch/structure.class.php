<?php

class detailedSearchElement extends structureElement
{
    use LocationProviderTrait;
    use ZxPictureTypesProvider;

    public $dataResourceName = 'module_detailedsearch';
    public $defaultActionName = 'show';
    public $role = 'container';

    protected $parameters;
    protected $resultsList;
    protected $musicList;
    protected $pager;
    protected $queryParameters;
    protected $sortParameters;
    protected $currentPageNumber;
    protected $startElementNumber;
    protected $endElementNumber;
    protected $totalAmount;
    protected $filtrationResult;
    const ELEMENTS_ON_PAGE = 60;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['items'] = 'text';

        $moduleStructure['titleWord'] = 'text';
        $moduleStructure['startYear'] = 'text';
        $moduleStructure['endYear'] = 'text';
        $moduleStructure['rating'] = 'text';
        $moduleStructure['country'] = 'text';
        $moduleStructure['city'] = 'text';
        $moduleStructure['partyPlace'] = 'text';
        $moduleStructure['sortParameter'] = 'text';
        $moduleStructure['sortOrder'] = 'text';
        $moduleStructure['pictureType'] = 'text';
        $moduleStructure['tagsInclude'] = 'text';
        $moduleStructure['tagsExclude'] = 'text';
        $moduleStructure['authorCountry'] = 'text';
        $moduleStructure['authorCity'] = 'text';
        $moduleStructure['resultsType'] = 'text';
        $moduleStructure['realtime'] = 'checkbox';
        $moduleStructure['inspiration'] = 'checkbox';
        $moduleStructure['stages'] = 'checkbox';
        $moduleStructure['formatGroup'] = 'text';
        $moduleStructure['format'] = 'text';
    }

    /**
     * @psalm-return list<mixed>
     */
    public function getMusicFormats(): array
    {
        $db = $this->getService('db');
        $formats = [];
        if ($records = $db->table('module_zxmusic')
            ->select('type')
            ->distinct()
            ->where('type', '!=', '')
            ->orderBy('type', 'asc')
            ->get()
        ) {
            $formats = array_column($records, 'type');
        }
        return $formats;
    }

    protected function getCityId()
    {
        return $this->authorCity;
    }

    protected function getCountryId()
    {
        return $this->authorCountry;
    }

    /**
     * @return string[]
     *
     * @psalm-return list{0?: 'titleWord', 1?: 'startYear', 2?: 'endYear', 3?: 'rating', 4?: 'country', 5?: 'city', 6?: 'partyPlace', 7?: 'sortParameter', 8?: 'sortOrder', 9?: 'formatGroup'|'pictureType', 10?: 'format'|'realtime', 11?: 'inspiration'|'realtime', 12?: 'stages'|'tagsInclude', 13?: 'tagsExclude'|'tagsInclude', 14?: 'authorCountry'|'tagsExclude', 15?: 'authorCity'|'authorCountry', 16?: 'authorCity'|'resultsType', 17?: 'resultsType'}
     */
    public function getParameterNames(): array
    {
        $parametersNames = [];
        if ($this->items == 'graphics') {
            $parametersNames = [
                'titleWord',
                'startYear',
                'endYear',
                'rating',
                'country',
                'city',
                'partyPlace',
                'sortParameter',
                'sortOrder',
                'pictureType',
                'realtime',
                'inspiration',
                'stages',
                'tagsInclude',
                'tagsExclude',
                'authorCountry',
                'authorCity',
                'resultsType',
            ];
        } elseif ($this->items == 'music') {
            $parametersNames = [
                'titleWord',
                'startYear',
                'endYear',
                'rating',
                'country',
                'city',
                'partyPlace',
                'sortParameter',
                'sortOrder',
                'formatGroup',
                'format',
                'realtime',
                'tagsInclude',
                'tagsExclude',
                'authorCountry',
                'authorCity',
                'resultsType',
            ];
        }
        return $parametersNames;
    }

    public function getParametersInfo()
    {
        if ($this->parameters === null) {
            $controller = controller::getInstance();
            $parametersNames = $this->getParameterNames();

            $this->parameters = [];
            $this->parameters['sortParameter'] = 'date';
            $this->parameters['sortOrder'] = 'desc';

            foreach ($parametersNames as $name) {
                if ($controller->getParameter($name)) {
                    $this->parameters[$name] = $controller->getParameter($name);
                }
            }
        }
        return $this->parameters;
    }

    protected function applyParameters(): void
    {
        if ($parameters = $this->getParametersInfo()) {
            foreach ($parameters as $name => &$value) {
                $this->$name = $value;
            }
        }
    }

    public function getQueryParameters()
    {
        if ($this->queryParameters === null) {
            $this->queryParameters = [];
            if ($parameters = $this->getParametersInfo()) {
                $startYear = false;
                $endYear = false;
                if (isset($parameters['startYear'])) {
                    $startYear = intval($parameters['startYear']);
                }
                if (isset($parameters['endYear'])) {
                    $endYear = intval($parameters['endYear']);
                }
                if (is_numeric($startYear) && !$endYear) {
                    $endYear = date('Y');
                }
                if (is_numeric($endYear) && !$startYear) {
                    $startYear = 1970;
                }

                if ($this->items == 'music') {
                    if (isset($parameters['titleWord'])) {
                        $this->queryParameters['zxMusicTitleSearch'] = $parameters['titleWord'];
                    }
                    if ($startYear && $endYear) {
                        if ($startYear > $endYear) {
                            [$startYear, $endYear] = [$endYear, $startYear];
                        }
                        $this->queryParameters['zxMusicYear'] = [];
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $this->queryParameters['zxMusicYear'][] = $year;
                        }
                    }
                    if (isset($parameters['partyPlace'])) {
                        $this->queryParameters['zxMusicMinPartyPlace'] = intval($parameters['partyPlace']);
                    }
                    if (isset($parameters['rating'])) {
                        $this->queryParameters['zxMusicMinRating'] = floatval($parameters['rating']);
                    }
                    if (isset($parameters['formatGroup'])) {
                        $this->queryParameters['zxMusicFormatGroup'] = $parameters['formatGroup'];
                    }
                    if (isset($parameters['format'])) {
                        $this->queryParameters['zxMusicFormat'] = $parameters['format'];
                    }
                    if (isset($parameters['realtime'])) {
                        $this->queryParameters['zxMusicCompo'] = [
                            'realtime',
                            'realtimeay',
                            'realtimebeeper',
                            'realtimec',
                        ];
                    }
                    if (isset($parameters['tagsInclude'])) {
                        $this->queryParameters['zxMusicTagsInclude'] = explode(',', $parameters['tagsInclude']);
                    }
                    if (isset($parameters['tagsExclude'])) {
                        $this->queryParameters['zxPictureTagsExclude'] = explode(',', $parameters['tagsExclude']);
                    }
                    if (!$this->queryParameters) {
                        $this->queryParameters['zxMusicAll'] = true;
                    }
                } else {
                    if (isset($parameters['titleWord'])) {
                        $this->queryParameters['zxPictureTitleSearch'] = $parameters['titleWord'];
                    }

                    if ($startYear && $endYear) {
                        if ($startYear > $endYear) {
                            [$startYear, $endYear] = [$endYear, $startYear];
                        }
                        $this->queryParameters['zxPictureYear'] = [];
                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $this->queryParameters['zxPictureYear'][] = $year;
                        }
                    }

                    if (isset($parameters['partyPlace'])) {
                        $this->queryParameters['zxPictureMinPartyPlace'] = intval($parameters['partyPlace']);
                    }
                    if (isset($parameters['rating'])) {
                        $this->queryParameters['zxPictureMinRating'] = floatval($parameters['rating']);
                    }
                    if (isset($parameters['pictureType'])) {
                        $this->queryParameters['zxPictureType'] = $parameters['pictureType'];
                    }
                    if (isset($parameters['realtime'])) {
                        $this->queryParameters['zxPictureCompo'] = ['realtime', 'realtimep'];
                    }
                    if (isset($parameters['inspiration'])) {
                        $this->queryParameters['zxPictureInspiration'] = true;
                    }
                    if (isset($parameters['stages'])) {
                        $this->queryParameters['zxPictureStages'] = true;
                    }

                    if (isset($parameters['tagsInclude'])) {
                        $this->queryParameters['zxPictureTagsInclude'] = explode(',', $parameters['tagsInclude']);
                    }
                    if (isset($parameters['tagsExclude'])) {
                        $this->queryParameters['zxPictureTagsExclude'] = explode(',', $parameters['tagsExclude']);
                    }

                    if (!$this->queryParameters) {
                        $this->queryParameters['zxPictureAll'] = true;
                    }
                }

                if (isset($parameters['authorCountry'])) {
                    $this->queryParameters['authorCountry'] = explode(',', $parameters['authorCountry']);
                }

                if (isset($parameters['authorCity'])) {
                    $this->queryParameters['authorCity'] = explode(',', $parameters['authorCity']);
                }

                if ($this->getResultsType() == 'author') {
                    if ($this->items == 'graphics') {
                        $this->queryParameters['authorOfItemType'] = 'authorPicture';
                    } elseif ($this->items == 'music') {
                        $this->queryParameters['authorOfItemType'] = 'authorMusic';
                    }
                }
            }
        }
        return $this->queryParameters;
    }

    public function getSortParameters()
    {
        if ($this->sortParameters === null) {
            $this->sortParameters = [];
            if ($parameters = $this->getParametersInfo()) {
                $this->sortParameters = [$parameters['sortParameter'] => $parameters['sortOrder']];
            }
        }
        return $this->sortParameters;
    }

    public function getStartElementNumber()
    {
        if ($this->startElementNumber === null) {
            $this->startElementNumber = 1;
            if ($parameters = $this->getParametersInfo()) {
                $this->startElementNumber = ($this->getCurrentPageNumber() - 1) * $this->getElementsOnPage() + 1;
            }
        }
        return $this->startElementNumber;
    }

    public function getEndElementNumber()
    {
        if ($this->endElementNumber === null) {
            $this->endElementNumber = $this->getStartElementNumber() + $this->getElementsOnPage() - 1;
            if ($this->endElementNumber > ($totalAmount = $this->getTotalAmount())) {
                $this->endElementNumber = $totalAmount;
            }
        }
        return $this->endElementNumber;
    }

    public function getCurrentPageNumber()
    {
        if ($this->currentPageNumber === null) {
            $this->currentPageNumber = 0;
            if ($parameters = $this->getParametersInfo()) {
                $controller = controller::getInstance();

                if ($controller->getParameter('page')) {
                    $this->currentPageNumber = intval($controller->getParameter('page'));
                } else {
                    $this->currentPageNumber = 1;
                }
            }
        }
        return $this->currentPageNumber;
    }

    public function getResultsList()
    {
        if ($this->resultsList === null) {
            $this->resultsList = [];
            $exportType = $this->getExportType();
            if ($result = $this->getFiltrationResult()) {
                if (isset($result[$exportType])) {
                    $this->resultsList = $result[$exportType];
                }
            }
        }
        return $this->resultsList;
    }

    public function getTotalAmount()
    {
        if ($this->totalAmount === null) {
            $this->totalAmount = 0;

            if ($result = $this->getFiltrationResult()) {
                $this->totalAmount = $result['totalAmount'];
            }
        }
        return $this->totalAmount;
    }

    protected function getExportType()
    {
        return $this->getResultsType();
    }

    public function getResultsType(): string
    {
        $type = 'zxPicture';
        if ($parameters = $this->getParametersInfo()) {
            if (isset($parameters['resultsType'])) {
                $type = $parameters['resultsType'];
            }
        }
        if ($type == 'author') {
            $type = "author";
        } elseif ($this->items == 'music') {
            $type = "zxMusic";
        } else {
            $type = "zxPicture";
        }
        return $type;
    }

    public function getElementsOnPage(): int
    {
        return self::ELEMENTS_ON_PAGE;
    }

    public function getPager()
    {
        if ($this->pager === null) {
            $this->pager = false;
            if ($parameters = $this->getParametersInfo()) {
                if ($result = $this->getFiltrationResult()) {
                    $baseURL = $this->URL;
                    foreach ($parameters as $title => &$value) {
                        $baseURL .= $title . ':' . $value . '/';
                    }
                    $this->pager = new pager(
                        $baseURL,
                        $result['totalAmount'],
                        $this->getElementsOnPage(),
                        $this->getCurrentPageNumber()
                    );
                }
            }
        }
        return $this->pager;
    }

    public function getFiltrationResult()
    {
        if ($this->filtrationResult === null) {
            $this->filtrationResult = false;

            $queryParameters = $this->getQueryParameters();
            $sortParameters = $this->getSortParameters();
            $startElementNumber = $this->getStartElementNumber() - 1;
            $elementsOnPage = $this->getElementsOnPage();
            $exportType = $this->getExportType();

            $api = $this->getService('ApiQueriesManager');
            if ($query = $api->getQuery()) {
                $query->setFiltrationParameters($queryParameters);
                $query->setExportType($exportType);
                $query->setOrder($sortParameters);
                $query->setStart($startElementNumber);
                $query->setLimit($elementsOnPage);
                $this->filtrationResult = $query->getQueryResult();
            }
        }
        return $this->filtrationResult;
    }

    public function getApiUrl(): string
    {
        $controller = controller::getInstance();
        $url = $controller->baseURL . 'api/';
        $url .= 'types:' . $this->getExportType() . '/';
        $url .= 'export:' . $this->getExportType() . '/';
        $url .= 'language:' . $this->getService('LanguagesManager')->getCurrentLanguageCode() . '/';
        $url .= 'start:' . ($this->getStartElementNumber() - 1) . '/';
        $url .= 'limit:' . $this->getElementsOnPage() . '/';
        if ($sortParameters = $this->getSortParameters()) {
            $url .= 'order:';
            foreach ($sortParameters as $parameterName => &$value) {
                $url .= $parameterName . ',' . $value . '/';
                break;
            }
        }
        if ($queryString = $this->generateQueryString()) {
            $url .= $queryString;
        }
        return $url;
    }

    public function getSaveUrl(): string
    {
        $controller = controller::getInstance();
        $url = $controller->baseURL . 'zipItems/';
        $url .= 'export:' . $this->getExportType() . '/';
        $url .= 'language:' . $this->getService('LanguagesManager')->getCurrentLanguageCode() . '/';
        $url .= 'structure:authors/';
        if ($queryString = $this->generateQueryString()) {
            $url .= $queryString;
        }
        return $url;
    }

    protected function generateQueryString(): string
    {
        $string = '';
        if ($parameters = $this->getQueryParameters()) {
            $string .= 'filter:';
            foreach ($parameters as $title => &$value) {
                if (is_array($value)) {
                    $string .= $title . '=' . implode(',', $value) . ';';
                } else {
                    $string .= $title . '=' . $value . ';';
                }
            }
        }
        return $string;
    }

    public function getFormData()
    {
        $this->applyParameters();
        return parent::getFormData();
    }
}

