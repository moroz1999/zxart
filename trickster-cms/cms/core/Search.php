<?php

use DI\Container;

class Search implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected array $types = [];
    protected array $filters = [];
    protected $offset = 0;
    protected $limit = 50;
    protected $partialMatching = false;
    protected $contentMatching = false;
    protected $singlePageCombining = false;
    protected bool $relevanceOrdering = false;
    protected string $input = '';
    protected $languageId;

    public function __construct(Container $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param mixed $languageId
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
    }

    public function setInput($input)
    {
        $this->input = (string)$input;
    }

    public function setTypes(array $types)
    {
        $this->types = $types;
    }

    public function setOffset($offset)
    {
        $this->offset = (int)$offset;
    }

    public function setLimit($limit)
    {
        $this->limit = (int)$limit;
    }

    public function setPartialMatching($enabled = true)
    {
        $this->partialMatching = (bool)$enabled;
    }

    public function setContentMatching($enabled = true)
    {
        $this->contentMatching = (bool)$enabled;
    }

    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    public function setSinglePageCombining($enabled = true)
    {
        $this->singlePageCombining = (bool)$enabled;
    }

    /**
     * Instant search shows one page only, so the closest matches have to be on top of it.
     * Paged search is ordered by title alone: every page is then a part of one alphabetical list.
     */
    public function setRelevanceOrdering(bool $enabled = true): void
    {
        $this->relevanceOrdering = $enabled;
    }

    public function getResult()
    {
        $result = new SearchResult();
        $this->types = array_unique($this->types);

        $this->performTypeSearch($result);
        $result->exactMatches = count($result->sets);
        if (!$result->exactMatches && $this->partialMatching) {
            $this->performTypeSearch($result, false, '_partial');
        }
        return $result;
    }

    protected function performTypeSearch($searchResult, $exact = true, $typePostfix = '', $exclusions = [])
    {
        $structureManager = $this->getService('structureManager');
        $queryFiltersManager = $this->getService(QueryFiltersManager::class);
        $idsByType = [];

        if ($exact) {
            $input = trim($this->input);
        } else {
            $input = $this->generateQueryStrings($this->input);
        }

        if ($input) {
            foreach ($this->types as &$type) {
                $queryParameters = [];
                if ($this->contentMatching) {
                    $queryParameters[$type . 'Search'] = $input;
                } else {
                    $queryParameters[$type . 'AjaxSearch'] = $input;
                }
                $queryParameters += $this->filters;
                if (!empty($exclusions[$type])) {
                    $queryParameters['structureSkipId'] = $exclusions[$type];
                }
                // temporary tables are skipped here: they would drop the ordering the results rely on
                $queryResult = $queryFiltersManager->getFilterQueries($queryParameters, [$type], true, false);
                if (!empty($queryResult[$type])) {
                    /** @var \Illuminate\Database\Query\Builder $typeQuery */
                    $typeQuery = $queryResult[$type];
                    $this->assignOrdering($typeQuery, $input);
                    $ids = array_column($typeQuery->get(), 'id');
                    $idsByType[$type . $typePostfix] = array_values(array_unique($ids));
                }
            }
            if ($idsByType) {
                if ($this->singlePageCombining) {
                    $averageAmount = floor($this->limit / count($idsByType));
                    $extraAmount = 0;
                    $typesWithExtra = 0;
                    foreach ($idsByType as $type => &$idList) {
                        $typeResultsAmount = count($idList);
                        if ($typeResultsAmount < $averageAmount) {
                            $extraAmount += $averageAmount - $typeResultsAmount;
                        } else {
                            $typesWithExtra++;
                        }
                    }

                    foreach ($idsByType as $type => &$idList) {
                        $customSetName = ucfirst($type) . 'SearchResultSet';
                        if (class_exists($customSetName)) {
                            $set = new $customSetName();
                        } else {
                            $set = new SearchResultSet();
                        }
                        $set->type = $type;
                        $set->partial = !$exact;

                        $typeResultsAmount = count($idList);
                        $set->setTotalCount($typeResultsAmount);
                        if ($typeResultsAmount < $averageAmount) {
                            $baseSliceAmount = $typeResultsAmount;
                        } else {
                            $baseSliceAmount = $averageAmount + round($extraAmount / $typesWithExtra);
                        }

                        //if some elements are not available by "getElementById" we get next elements what were sliced in $idList
                        $elements = [];
                        $slicedAmount = 0;
                        while ($baseSliceAmount) {
                            $slicedIdList = array_slice($idList, $slicedAmount, $baseSliceAmount);
                            $slicedAmount += $baseSliceAmount;
                            $baseSliceAmount = 0;
                            foreach ($slicedIdList as $elementId) {
                                if ($element = $structureManager->getElementById($elementId, $this->languageId)) {
                                    $elements[] = $element;
                                } else {
                                    $baseSliceAmount++;
                                    $set->setTotalCount($set->getTotalCount() - 1);
                                }
                            }
                        }

                        foreach ($elements as $element) {
                            $set->elements[] = $element;
                            $searchResult->elements[] = $element;
                        }
                        $searchResult->sets[] = $set;
                        $searchResult->count += count($elements);
                    }
                } else {
                    $resultsNeeded = $this->limit;
                    foreach ($idsByType as $type => &$idList) {
                        $queriesResults[$type . $typePostfix] = $idsByType[$type];
                        $typeResultsCount = count($idsByType[$type]);
                        if ($resultsNeeded > 0) {
                            $set = new SearchResultSet();
                            $set->type = $type;
                            $set->partial = !$exact;

                            $i = $searchResult->count;
                            foreach ($idList as $elementId) {
                                if (++$i > $this->offset) {
                                    if ($element = $structureManager->getElementById($elementId)) {
                                        $set->elements[] = $element;
                                        $searchResult->elements[] = $element;
                                    }
                                    --$resultsNeeded;
                                    if ($resultsNeeded == 0) {
                                        break;
                                    }
                                }
                            }
                            $searchResult->sets[] = $set;
                        }
                        $searchResult->count += $typeResultsCount;
                    }
                }
            }
        }
        return $idsByType;
    }

    /**
     * Orders the whole result set in the database. Instant search puts the closest title matches
     * on top of its single page; paged search is ordered by title alone, so that every page
     * is a part of one and the same alphabetical list.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|string[] $input
     * @return void
     */
    protected function assignOrdering($query, $input)
    {
        $sortColumn = searchQueryFilter::SORT_TITLE_COLUMN;
        if (!$this->hasSortColumn($query, $sortColumn)) {
            return;
        }
        if ($this->relevanceOrdering) {
            $words = array_values((array)$input);
            $wrappedColumn = $query->getGrammar()->wrap($sortColumn);
            $patterns = [
                static fn(string $word): string => $word,
                static fn(string $word): string => $word . '%',
                static fn(string $word): string => '%' . $word . '%',
            ];
            foreach ($patterns as $makePattern) {
                $conditions = array_fill(0, count($words), $wrappedColumn . ' like ?');
                $query->orderByRaw(
                    '(' . implode(' or ', $conditions) . ') desc',
                    array_map($makePattern, $words)
                );
            }
        }
        $query->orderBy($sortColumn);
    }

    /**
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $sortColumn
     * @return bool
     */
    protected function hasSortColumn($query, $sortColumn)
    {
        $columns = array_filter($query->columns, 'is_string');
        foreach ($columns as $column) {
            if (str_ends_with($column, ' as ' . $sortColumn)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $query
     * @return string[]
     */
    protected function generateQueryStrings($query)
    {
        $queryStrings = [];
        $words = explode(" ", $query);
        foreach ($words as &$word) {
            $word = trim($word);
            if (mb_strlen(trim($word)) > 2) {
                $queryStrings[] = $word;
            }
        }

        return $queryStrings;
    }
}

