<?php


class ApiQueryResultResolver implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    public function resolve(
        ?array $filterQueries,
        string $exportType,
        array $resultTypes,
        ?array $order,
        ?int $start,
        ?int $limit
    ): array {
        $queryResult = [];
        $exportedTypeIdList = [];
        if (isset($filterQueries[$exportType])) {
            $exportTypeQuery = $filterQueries[$exportType];
        } else {
            $exportTypeQuery = null;
        }
        $resolvers = [
            'zxPicture' => PicturesManager::class,
            'zxProd' => ProdsManager::class,
            'zxRelease' => ReleasesResolver::class,
            'zxMusic' => MusicManager::class,
            'author' => AuthorsManager::class,
            'group' => GroupsManager::class,
            'party' => PartiesManager::class,
        ];
        if (isset($resolvers[$exportType])) {
            $resolverClass = $resolvers[$exportType];
        }
        if ($resolverClass) {
            $resolverManager = $this->getService($resolverClass);
            $queryResult[$exportType] = $resolverManager->getElementsByQuery(
                $exportTypeQuery,
                $order,
                $start,
                $limit
            );
            if ($exportTypeQuery) {
                $queryResult['totalAmount'] = $exportTypeQuery->count();
            } else {
                $queryResult['totalAmount'] = $resolverManager->makeQuery()->count();
            }
            foreach ($queryResult[$exportType] as $entity) {
                $exportedTypeIdList[] = $entity->id;
            }
        } elseif ($exportType === 'zxRelease') {
            /**
             * @var ProdsManager $prodsManager
             */
            $prodsManager = $this->getService('ProdsManager');
            $queryResult[$exportType] = $prodsManager->getReleasesByIdList(
                $exportTypeQuery,
                $order,
                $start,
                $limit
            );
            if ($exportTypeQuery) {
                $queryResult['totalAmount'] = $exportTypeQuery->count();
            } else {
                $queryResult['totalAmount'] = 0;
            }
            foreach ($queryResult[$exportType] as $entity) {
                $exportedTypeIdList[] = $entity->id;
            }
        } else {
            $structureManager = $this->getService('structureManager');
            if ($records = $exportTypeQuery->get()) {
                foreach ($records as $record) {
                    if (($element = $structureManager->getElementById(
                            $record['id']
                        )) && ($element->structureType === $exportType)) {
                        $queryResult[$exportType][] = $element;
                    }
                }
            }
            $queryResult['totalAmount'] = count($queryResult[$exportType]);
        }

        // make final conversion queries
        /**
         * @var QueryFiltersManager $queryFiltersManager
         */
        $queryFiltersManager = $this->getService('QueryFiltersManager');
        foreach ($resultTypes as $typeName) {
            if ($typeName === 'author' && $typeName != $exportType) {
                $query = $queryFiltersManager->convertTypeData(
                    $exportedTypeIdList,
                    $typeName,
                    $exportType,
                    $filterQueries
                );
                $authorsManager = $this->getService('AuthorsManager');
                $queryResult[$typeName] = $authorsManager->getElementsByQuery($query);
            } elseif (($typeName === 'authorAlias' || $typeName === 'groupAlias') && $typeName != $exportType) {
                $query = $queryFiltersManager->convertTypeData(
                    $exportedTypeIdList,
                    $typeName,
                    $exportType,
                    $filterQueries
                );
                $queryResult[$typeName] = [];
                $structureManager = $this->getService('structureManager');
                foreach ($query->get('id') as $id) {
                    if (($element = $structureManager->getElementById(
                            $id
                        )) && ($element->structureType === $typeName)) {
                        $queryResult[$typeName][] = $element;
                    }
                }
            } elseif (($typeName === 'zxRelease') && $typeName != $exportType) {
                $query = $queryFiltersManager->convertTypeData(
                    $exportedTypeIdList,
                    $typeName,
                    $exportType,
                    $filterQueries
                );
                $prodsManager = $this->getService('ProdsManager');
                $queryResult[$typeName] = $prodsManager->getReleasesByIdList(
                    $query
                );
            } elseif (($typeName === 'zxProd') && $typeName != $exportType) {
                $query = $queryFiltersManager->convertTypeData(
                    $exportedTypeIdList,
                    $typeName,
                    $exportType,
                    $filterQueries
                );
                $prodsManager = $this->getService('ProdsManager');
                $queryResult[$typeName] = $prodsManager->getElementsByQuery(
                    $query
                );
            }
        }
        return $queryResult;
    }
}