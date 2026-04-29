<?php

use App\Paths\PathsManager;

class QueryFiltersManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    private $tables = [];

    /**
     * @param $filterName
     * @return bool|QueryFilter
     */
    protected function getFilter($filterName)
    {
        $filter = false;
        $className = $filterName . 'QueryFilter';
        if (!class_exists($className, false)) {
            $fileName = $filterName . '.class.php';
            $pathsManager = $this->getService(PathsManager::class);
            $fileDirectory = $pathsManager->getRelativePath('queryFilters');
            if ($filePath = $pathsManager->getIncludeFilePath($fileDirectory . $fileName)) {
                include_once($filePath);
            }
        }
        if (class_exists($className, false)) {
            $filter = new $className();
            if ($filter instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($filter);
            }
        }
        return $filter;
    }

    /**
     * @param $type
     * @return bool|QueryFilterConverter
     */
    public function getConverter($type)
    {
        $converter = false;
        $className = $type . 'QueryFilterConverter';
        if (!class_exists($className, false)) {
            $fileName = $type . 'QueryFilterConverter.class.php';
            $pathsManager = $this->getService(PathsManager::class);
            $fileDirectory = $pathsManager->getRelativePath('queryFilterConverters');
            if ($filePath = $pathsManager->getIncludeFilePath($fileDirectory . $fileName)) {
                include_once($filePath);
            }
        }
        if (class_exists($className, false)) {
            $converter = new $className();
            if ($converter instanceof DependencyInjectionContextInterface) {
                $this->instantiateContext($converter);
            }
            $converter->setType($type);
        } else {
            $this->logError('queryFilterConverter class "' . $className . '" is missing');
        }
        return $converter;
    }

    /**
     * @param array $parameters - Filter types
     * @param array $resultTypes - Object types
     * @param bool $optimized - enable parameters sorting top optimized result query
     * @return array|bool
     */
    public function getFilterQueries($parameters, $resultTypes, $optimized = true, $wrapTemporaryTables = true)
    {
        $queries = $this->compileResultsIndex($resultTypes);
        if ($groupResultQuery = $this->getFilterQuery($parameters, $resultTypes, $optimized)) {
            foreach ($queries as $type => $value) {
                if ($wrapTemporaryTables) {
                    $queries[$type] = $this->wrapTemporaryTable($groupResultQuery[$type], $parameters, $type);
                } else {
                    $queries[$type] = $groupResultQuery[$type];
                }
            }
        }
        return $queries;
    }

    protected function wrapTemporaryTable($query, $parameters, $type)
    {
        $tableName = md5(json_encode([$parameters, $type]));
        $db = $this->getService('db');

        if (!isset($this->tables[$tableName])) {
            $this->tables[$tableName] = true;
            $sql = $query->toSql();
            $db->insert($db->raw("DROP TEMPORARY TABLE IF EXISTS {$db->getTablePrefix()}{$tableName}"));
            $db->insert(
                $db->raw("CREATE TEMPORARY TABLE {$db->getTablePrefix()}{$tableName} 
                (INDEX (id))                
                " . $sql),
                $query->getBindings()
            );
        }
        return $db->table($tableName);
    }

    /**
     * @param array $parametersList IDs list from one filter
     * @param array $resultTypes Object types
     * @param $optimized - sort the parameters to get less type conversions and simplier query
     * @return array|bool
     */
    protected function getFilterQuery($parametersList, $resultTypes, $optimized)
    {
        $resultNotFound = false;

        if ($optimized) {
            $parametersList = $this->optimizeParametersList($parametersList, $resultTypes);
        }

        $groupResults = $this->compileResultsIndex($resultTypes);
        $filterResult = false;
        $cachedResults = [];
        $previousType = false;
        foreach ($parametersList as $filterName => $filterArgument) {
            if ($filter = $this->getFilter($filterName)) {
                $incomingType = $filter->getRequiredType();
                if ($nextFilterData = $this->compileNextFilterData(
                    $filterResult,
                    $incomingType,
                    $previousType,
                    $cachedResults
                )) {
                    $cachedResults[$incomingType] = $nextFilterData;
                }
                if ($filterResult = $filter->getFilteredIdList($filterArgument, $nextFilterData)) {
                    $cachedResults[$incomingType] = $filterResult;
                } else {
                    $resultNotFound = true;
                    break;
                }
                $previousType = $incomingType;
            }
        }
        if (!$resultNotFound) {
            foreach ($resultTypes as &$type) {
                if ($type == $previousType) {
                    // use filter's query results for this type
                    $groupResults[$type] = $filterResult;
                } else {
                    // filtration did not query this type, perform a secondary filtration
                    // using the results from main filtration
                    $groupResults[$type] = $this->convertTypeData($filterResult, $type, $previousType, $cachedResults);
                }
            }
        }
        return $groupResults;
    }

    public function optimizeParametersList($parametersList, $resultTypes)
    {
        $typesIndex = array_flip($resultTypes);

        $parametersInfo = [];
        foreach ($parametersList as $type => $value) {
            $parametersInfo[] = [$type, $value];
        }

        usort(
            $parametersInfo,
            function ($a, $b) use ($typesIndex) {
                if (($filter1 = $this->getFilter($a[0])) && ($filter2 = $this->getFilter($b[0]))) {
                    $type1 = $filter1->getRequiredType();
                    $type2 = $filter2->getRequiredType();
                    if (isset($typesIndex[$type1])) {
                        if (isset($typesIndex[$type2])) {
                            return strcmp($type1, $type2);
                        }
                        return 1;
                    } elseif (isset($typesIndex[$type2])) {
                        return -1;
                    }
                    return strcmp($type1, $type2);
                }
                return 0;
            }
        );

        $parametersList = [];
        foreach ($parametersInfo as $info) {
            $parametersList[$info[0]] = $info[1];
        }

        return $parametersList;
    }

    /**
     * @param Illuminate\Database\Query\Builder|array $sourceData
     * @param string $targetType
     * @param string $sourceType
     * @param Illuminate\Database\Query\Builder[]|[][] $cachedResults
     * @return Illuminate\Database\Query\Builder
     */
    public function convertTypeData($sourceData, $targetType, $sourceType, $cachedResults)
    {
        $convertedData = false;
        if ($converter = $this->getConverter($targetType)) {
            if (isset($cachedResults[$targetType])) {
                $converter->setCorrectionQuery($cachedResults[$targetType]);
            }
            $convertedData = $converter->convert($sourceData, $sourceType);
        }
        return $convertedData;
    }

    protected function compileNextFilterData($filterResult, $incomingType, $previousType, $cachedResults)
    {
        $nextFilterData = false;
        if (!$incomingType || $previousType == $incomingType) {
            $nextFilterData = $filterResult;
        } elseif ($incomingType) {
            $nextFilterData = $this->convertTypeData($filterResult, $incomingType, $previousType, $cachedResults);
        }
        return $nextFilterData;
    }

    protected function compileResultsIndex($resultTypes)
    {
        $finalResultsList = [];
        foreach ($resultTypes as $type) {
            $finalResultsList[$type] = [];
        }
        return $finalResultsList;
    }
}

