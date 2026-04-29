<?php

class Dashboard
{
    /**
     * @var structureManager
     */
    protected $structureManager;
    /**
     * @var
     */
    protected $db;

    /**
     * @param structureManager $structureManager
     */
    public function setStructureManager($structureManager)
    {
        $this->structureManager = $structureManager;
    }

    /**
     * @param mixed $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    public function getLatestAddedElements()
    {
        $query = $this->db->table('structure_elements')
            ->select('id')
            ->orderBy('id', 'desc')
            ->limit(10);

        $elements = [];

        foreach ($query->get() as $row) {
            if ($element = $this->structureManager->getElementById($row['id'])) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    public function getNonSentOrders()
    {
        /**
         * @var ordersElement $ordersElement
         */
        if ($ordersElement = $this->structureManager->getElementByMarker('orders')) {
            return $ordersElement->getOrders(
                [
                    'limit' => 10,
                    'sort' => 'dueDate',
                    'order' => 'desc',
                    'filter_order_status' => [
                        'payed',
                        'undefined',
                        'failed',
                    ],
                ]
            );
        }
        return false;
    }

    public function getLatestRegisteredUsers()
    {
        if ($usersElement = $this->structureManager->getElementByMarker('users')) {
            return $usersElement->getUsers(
                [
                    'limit' => 5,
                    'sort' => 'id',
                    'order' => 'desc',
                ]
            );
        }
        return false;
    }

    public function getLatest404()
    {
        if ($logElement = $this->structureManager->getElementByMarker('notFoundLog')) {
            return $logElement->getLogs(
                [
                    'limit' => 5,
                    'sort' => 'id',
                    'order' => 'desc',
                ]
            );
        }
        return false;
    }

    public function getLatestUsers()
    {
        if ($usersElement = $this->structureManager->getElementByMarker('users')) {
            return $usersElement->getUsers(
                [
                    'limit' => 5,
                    'subscribe' => 1,
                    'sort' => 'id',
                    'order' => 'desc',
                ]
            );
        }
        return false;
    }

    public function getLatestActionsLogs()
    {
        if ($actionsLogElement = $this->structureManager->getElementByMarker('actionsLog')) {
            return $actionsLogElement->getLogs(
                [
                    'limit' => 5,
                    'sort' => 'date',
                    'order' => 'desc',
                ]
            );
        }
        return false;
    }

    public function getLatestTopErrors()
    {
        if ($logViewerElement = $this->getLogViewer()) {
            return $logViewerElement->getLatestTopErrors(5);
        }
        return false;
    }

    public function getLogViewer()
    {
        if ($logViewerElement = $this->structureManager->getElementByMarker('logViewer')) {
            return $logViewerElement;
        }
        return false;
    }

    public function getOrdersElement()
    {
        if ($ordersElement = $this->structureManager->getElementByMarker('orders')) {
            return $ordersElement;
        }
        return false;
    }

    public function getUsersElement()
    {
        if ($usersElement = $this->structureManager->getElementByMarker('users')) {
            return $usersElement;
        }
        return false;
    }

    public function getNotFoundLogElement()
    {
        if ($notFoundLogElement = $this->structureManager->getElementByMarker('notFoundLog')) {
            return $notFoundLogElement;
        }
        return false;
    }

    public function getActionsLogElement()
    {
        if ($actionsLogElement = $this->structureManager->getElementByMarker('actionsLog')) {
            return $actionsLogElement;
        }
        return false;
    }

    public function getTotalOrdersByDay()
    {
        /**
         * @var ordersElement $ordersElement
         */
        if ($ordersElement = $this->structureManager->getElementByMarker('orders')) {
            $limit = 30;

            $days = $ordersElement->getTotalOrdersByDay($limit);

            $labels = [];
            $data = [];

            for ($i = $limit; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime('today - ' . $i . ' days'));
                $labels[] = $date;
                if (isset($days[$date])) {
                    $data[] = $days[$date];
                } else {
                    $data[] = 0;
                }
            }

            return json_encode([
                1 => [
                    'labels' => $labels,
                    'data' => $data,
                ],
            ]);
        }
        return false;
    }
}