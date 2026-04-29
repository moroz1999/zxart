<?php

class pager
{
    private $baseURL = false;
    public $elementsCount = false;
    private $parameterName = '';
    private $pagesAmount = false;
    private $visibleAmount = false;
    private $useGetParameters = false;
    public $elementsOnPage = 0;
    public $startElement = 0;
    public $currentPage = 0;
    public $previousPage = [];
    public $nextPage = [];
    public $pagesList = [];

    public function __construct(
        $baseURL,
        $elementsCount,
        $elementsOnPage = 10,
        $currentPage = 1,
        $parameterName = 'page',
        $visibleAmount = 2,
        $useGetParameters = false
    ) {
        $this->currentPage = $currentPage;
        $this->elementsCount = $elementsCount;
        $this->elementsOnPage = $elementsOnPage;
        $this->baseURL = $baseURL;
        $this->parameterName = $parameterName;
        $this->visibleAmount = $visibleAmount;
        $this->useGetParameters = $useGetParameters;

        $this->calculate();
    }

    private function calculate()
    {
        $this->pagesAmount = ceil($this->elementsCount / $this->elementsOnPage);

        if ($this->currentPage > $this->pagesAmount) {
            $this->currentPage = $this->pagesAmount;
        } elseif ($this->currentPage < 1) {
            $this->currentPage = 1;
        }

        $this->startElement = ($this->currentPage - 1) * $this->elementsOnPage;

        $this->previousPage['active'] = false;
        if ($this->currentPage != 1) {
            $this->previousPage['active'] = true;
            $this->previousPage['URL'] = $this->generatePageUrl($this->currentPage - 1);
        }
        $this->nextPage['active'] = false;
        if ($this->currentPage != $this->pagesAmount) {
            $this->nextPage['active'] = true;
            $this->nextPage['URL'] = $this->generatePageUrl($this->currentPage + 1);
        }

        $start = $this->currentPage - $this->visibleAmount;
        $end = $this->currentPage + $this->visibleAmount;

        if ($this->currentPage <= $this->visibleAmount * 2) {
            $end = $this->visibleAmount * 2 + 3;
        }
        if ($this->currentPage >= $this->pagesAmount - $this->visibleAmount * 2) {
            $start = $this->pagesAmount - $this->visibleAmount * 2 - 2;
        }

        if ($start < 1) {
            $start = 1;
        }
        if ($end > $this->pagesAmount) {
            $end = $this->pagesAmount;
        }
        if ($start > 1) {
            $this->pagesList[] = $this->newPageElement(1);
        }
        if ($start > 2) {
            $this->pagesList[] = $this->newPageElement();
        }

        for ($index = $start; $index <= $end; $index++) {
            $this->pagesList[] = $this->newPageElement($index);
        }

        if ($end < $this->pagesAmount - 1) {
            $this->pagesList[] = $this->newPageElement();
        }
        if ($end < $this->pagesAmount) {
            $this->pagesList[] = $this->newPageElement($this->pagesAmount);
        }
    }

    private function newPageElement($number = false)
    {
        $element = [];
        $element['number'] = $number;
        if (is_numeric($number)) {
            $element['URL'] = $this->generatePageUrl($number);
            $element['active'] = false;
            if ($element['number'] == $this->currentPage) {
                $element['active'] = true;
            }
        }
        return $element;
    }

    protected function generatePageUrl($pageNumber)
    {
        $result = $this->baseURL;
        if ($this->useGetParameters) {
            if (strpos($result, '?') !== false) {
                $result .= '&';
            } else {
                $result .= '?';
            }
            $result .= "{$this->parameterName}=$pageNumber";
        } else {
            if (strpos($result, $this->parameterName . ':') !== false) {
                $result = preg_replace("|({$this->parameterName}:*./)|", '', $result);
            }
            $result .= "{$this->parameterName}:$pageNumber/";
        }
        $queryParams = isset($_GET) ? $_GET : [];
        if ($this->useGetParameters && isset($queryParams[$this->parameterName])) {
            unset($queryParams[$this->parameterName]);
        }
        if ($queryParams) {
            $result .= $this->useGetParameters ? '&' : '?';
            $result .= http_build_query($queryParams);
        }
        return $result;
    }

    public function getPagesAmount()
    {
        return $this->pagesAmount;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }
}

