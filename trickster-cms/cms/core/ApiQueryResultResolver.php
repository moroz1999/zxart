<?php


class ApiQueryResultResolver implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    public function resolve(
        array $filterQueries,
        string $exportType,
        array $resultTypes,
        array $order,
        int $start,
        int $limit
    ): array {
        $queryResult = [
            $exportType => [],
        ];
        $structureManager = $this->getService('structureManager');
        if ($records = $filterQueries[$exportType]->get()) {
            foreach ($records as $record) {
                if (($element = $structureManager->getElementById(
                        $record['id']
                    )) && ($element->structureType === $exportType)) {
                    $queryResult[$exportType][] = $element;
                }
            }
        }
        $queryResult['totalAmount'] = count($queryResult[$exportType]);
        return $queryResult;
    }
}