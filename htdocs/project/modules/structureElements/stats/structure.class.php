<?php

class statsElement extends structureElement
{
    use ChartDataProviderTrait;

    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';

    public $picturesList = [];
    public $pager;

    private $chartDataEventTypes;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getAllYearsData($table = 'zxpicture')
    {
        $yearsData = [
            'labels' => [],
            'data' => [],
        ];
        $collection = persistableCollection::getInstance('module_' . $table);

        $columns = ['year, count(*)'];

        $conditions = [];
        $conditions[] = ['column' => 'year', 'action' => '!=', 'argument' => 0];

        $orderFields = [
            'year' => 'asc',
        ];

        $result = $collection->conditionalLoad($columns, $conditions, $orderFields, null, ['year'], true);
        foreach ($result as $row) {
            $yearsData['labels'][] = $row['year'];
            $yearsData['data'][] = $row['count(*)'];
        }

        return json_encode($yearsData);
    }

    public function getRatedYearsData($table = 'zxpicture')
    {
        $yearsData = [
            'labels' => [],
            'data' => [],
        ];
        $collection = persistableCollection::getInstance('module_' . $table);

        $columns = ['year, count(*)'];

        $conditions = [];
        $conditions[] = ['column' => 'year', 'action' => '!=', 'argument' => 0];
        $conditions[] = [
            'column' => 'votes',
            'action' => '>',
            'argument' => $this->getService('ConfigManager')->get('zx.averageVote'),
        ];

        $orderFields = [
            'year' => 'asc',
        ];

        $result = $collection->conditionalLoad($columns, $conditions, $orderFields, null, ['year'], true);
        foreach ($result as $row) {
            $yearsData['labels'][] = $row['year'];
            $yearsData['data'][] = $row['count(*)'];
        }

        return json_encode($yearsData);
    }

    public function getViewsHistoryData()
    {
        $this->resetChartData();
        $this->chartDataEventTypes = ['view'];
        return $this->getChartData();
    }
    public function getRunsHistoryData()
    {
        $this->resetChartData();
        $this->chartDataEventTypes = ['view'];
        return $this->getChartData();
    }

    public function getPlaysHistoryData()
    {
        $this->resetChartData();
        $this->chartDataEventTypes = ['play'];
        return $this->getChartData();
    }

    public function getVotesHistoryData()
    {
        $this->resetChartData();
        $this->chartDataEventTypes = ['vote'];
        return $this->getChartData();
    }

    public function getCommentsHistoryData()
    {
        $this->resetChartData();
        $this->chartDataEventTypes = ['comment'];
        return $this->getChartData();
    }

    public function getUploadsHistoryData()
    {
        $this->resetChartData();
        $this->chartDataEventTypes = ['addZxPicture', 'addZxMusic'];
        return $this->getChartData();
    }

    public function getChartDataIds($type = null)
    {
        return null;
    }

    public function getChartDataEventTypes($type = null)
    {
        return $this->chartDataEventTypes;
    }

    public function getTopActionsUsers(string $moduleType, string $actionType, int $limit)
    {
        $data = [];
        /**
         * @var ActionsLogRepository $actionsLogRepository
         */
        if ($actionsLogRepository = $this->getService(ActionsLogRepository::class)) {
            if ($records = $actionsLogRepository->getTopUsersByAction($moduleType, $actionType, $limit)) {
                foreach ($records as $record) {
                    if ($userElement = $this->getUser($record['userId'])) {
                        $data[] = [
                            'user' => $userElement,
                            'count' => $record['amount']
                        ];
                    }
                }
            }
        }
        return $data;
    }

    public function getTopWorksUsers($type = 'addZxPicture', $limit = 10)
    {
        $data = [];
        /**
         * @var eventsLog $eventsLog
         */
        if ($eventsLog = $this->getService('eventsLog')) {
            if ($counts = $eventsLog->countEvents([$type], null, null, null, null, 'count', 'desc', $limit, 'userId')) {
                foreach ($counts as $userId => $count) {
                    if ($userElement = $this->getUser($userId)) {
                        $data[] = ['user' => $userElement, 'count' => $count];
                    }
                }
            }
        }
        return $data;
    }

    public function getTopVotesUsers($limit = 10)
    {
        $data = [];

        $db = $this->getService('db');

        if ($rows = $db->table('votes_history')
            ->selectRaw('count(id), userId')
            ->orderByRaw('count(id) desc')
            ->groupBy('userId')
            ->where('type', '!=', 'comment')
            ->limit($limit)
            ->get()
        ) {
            foreach ($rows as $row) {
                if ($userElement = $this->getUser($row['userId'])) {
                    $data[] = ['user' => $userElement, 'count' => $row['count(id)']];
                }
            }
        }
        return $data;
    }

    public function getUser($userId)
    {
        $structureManager = $this->getService('structureManager');
        $user = $structureManager->getElementById($userId, null, true);
        return $user;
    }
}

