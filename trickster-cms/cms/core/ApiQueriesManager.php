<?php

class ApiQueriesManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    public function getQueryFromString($parametersString)
    {
        $apiQuery = false;
        if ($info = $this->parseParametersString($parametersString)) {
            if ($info['exportType']) {
                $apiQuery = $this->getQuery();
                $apiQuery->setFiltrationParameters($info['queryParameters']);
                $apiQuery->setExportType($info['exportType']);
                $apiQuery->setLimit($info['limitQuantity'] ?? 1000);
                $apiQuery->setOrder($info['limitOrder']);
                $apiQuery->setStart($info['limitStart']);
                $apiQuery->setResultTypes($info['resultTypes']);
            }
        }
        return $apiQuery;
    }

    /**
     * @return ApiQuery
     */
    public function getQuery()
    {
        $query = new ApiQuery();
        $this->instantiateContext($query);
        return $query;
    }

    protected function parseParametersString($uriString)
    {
        $parameters = [];
        if ($uriStrings = explode('/', $uriString)) {
            foreach ($uriStrings as &$uriStringPart) {
                if (strpos($uriStringPart, QUERY_PARAMETERS_SEPARATOR) !== false) {
                    $strings = explode(QUERY_PARAMETERS_SEPARATOR, $uriStringPart);
                    $parameters[$strings[0]] = $strings[1];
                }
            }
        }

        $resultTypes = [];
        $limitStart = 0;
        $limitQuantity = null;
        $exportType = null;
        $limitOrder = null;
        $queryParameters = [];

        if (isset($parameters['types'])) {
            $typesStrings = explode(',', $parameters['types']);
            foreach ($typesStrings as &$typeString) {
                if (trim($typeString) != "") {
                    $resultTypes[] = $typeString;
                }
            }
        }

        if (isset($parameters['start'])) {
            $limitStart = intval($parameters['start']);
        }
        if (isset($parameters['limit'])) {
            $limitQuantity = intval($parameters['limit']);
        }
        if (isset($parameters['export'])) {
            $exportType = trim($parameters['export']);
        }
        if (isset($parameters['order'])) {
            $orderStrings = explode(',', $parameters['order']);
            if (isset($orderStrings[1])) {
                $limitOrder = [$orderStrings[0] => $orderStrings[1]];
            } else {
                $limitOrder = [$orderStrings[0] => 'asc'];
            }
        }

        if (isset($parameters['filter'])) {
            $filtersStrings = explode(';', $parameters['filter']);
            foreach ($filtersStrings as &$filterString) {
                if (trim($filterString) != '') {
                    $subStrings = explode('=', $filterString);
                    if (isset($subStrings[0])) {
                        $filterName = $subStrings[0];
                        if (isset($subStrings[1])) {
                            $queryParameters[$filterName] = explode(',', $subStrings[1]);
                        } else {
                            $queryParameters[$filterName] = true;
                        }
                    }
                }
            }
        }
        return [
            'resultTypes' => $resultTypes,
            'limitStart' => $limitStart,
            'limitQuantity' => $limitQuantity,
            'exportType' => $exportType,
            'limitOrder' => $limitOrder,
            'queryParameters' => $queryParameters,

        ];
    }
}
