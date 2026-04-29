<?php

class filterContentShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $structureElement->idList = [];

        $searchString = false;
        if ($controller->getParameter('search')) {
            $searchString = $controller->getParameter('search');
        }

        if (strlen($searchString) > 0) {
            $collection = persistableCollection::getInstance('module_product');

            $columns = ['id'];

            $conditions = [];
            $conditions[] = ['column' => 'title', 'action' => 'like', 'argument' => '%' . $searchString . '%'];

            $orderFields = ['title' => '1'];

            $result = $collection->conditionalLoad($columns, $conditions, $orderFields);
            $idIndex = [];
            foreach ($result as &$row) {
                if (!isset($idIndex[$row['id']])) {
                    $idIndex[$row['id']] = true;
                    $structureElement->idList[] = $row['id'];
                }
            }
        }
        $structureElement->setViewName('idList');
    }
}

