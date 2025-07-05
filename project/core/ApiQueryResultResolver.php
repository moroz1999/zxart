<?php


use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Parties\Services\PartiesService;
use ZxArt\Pictures\Services\PicturesManager;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Tunes\Services\TunesManager;

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
        $resolverClass = null;
        $resolvers = [
            'zxPicture' => PicturesManager::class,
            'zxProd' => ProdsService::class,
            'zxRelease' => ReleasesResolver::class,
            'zxMusic' => TunesManager::class,
            'author' => AuthorsService::class,
            'group' => GroupsService::class,
            'party' => PartiesService::class,
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
                $copy = clone $exportTypeQuery;
                if (!empty($copy->offset)){
                    $copy->offset(null);
                }
                $queryResult['totalAmount'] = $copy->count();
            } else {
                $queryResult['totalAmount'] = $resolverManager->makeQuery()->count();
            }
            foreach ($queryResult[$exportType] as $entity) {
                $exportedTypeIdList[] = $entity->id;
            }
        } else {
            $queryResult[$exportType] = [];
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
                $authorsManager = $this->getService(AuthorsService::class);
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
                $prodsManager = $this->getService(ProdsService::class);
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
                $prodsManager = $this->getService(ProdsService::class);
                $queryResult[$typeName] = $prodsManager->getElementsByQuery(
                    $query
                );
            }
        }
        return $queryResult;
    }
}