<?php

use App\Logging\EventsLog;

class statsElement extends structureElement
{
    use ChartDataProviderTrait;

    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';

    public $picturesList = [];
    public $pager;

    private $chartDataEventTypes;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    /**
     * @return false|string
     */
    public function getAllYearsData($table = 'zxpicture'): string|false
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

    /**
     * @return false|string
     */
    public function getRatedYearsData($table = 'zxpicture'): string|false
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
            'argument' => $this->getService(ConfigManager::class)->get('zx.averageVote'),
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

    /**
     * @return array[]
     *
     * @psalm-return list{0?: array{user: mixed, count: mixed},...}
     */
    public function getTopActionsUsers(string $moduleType, string $actionType, int $limit): array
    {
        $data = [];

        $actionsLogRepository = $this->getService(ActionsLogRepository::class);
        if ($records = $actionsLogRepository->getTopUsersByAction($moduleType, $actionType, $limit)) {
            foreach ($records as $record) {
                if ($userElement = $this->getUserElement($record['userId'])) {
                    $data[] = [
                        'user' => $userElement,
                        'count' => $record['amount']
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @return array[]
     *
     * @psalm-return list{0?: array{user: mixed, count: mixed},...}
     */
    public function getTopWorksUsers($type = 'addZxPicture', $limit = 10): array
    {
        $data = [];
        /**
         * @var EventsLog $eventsLog
         */
        $eventsLog = $this->getService(EventsLog::class);

        if ($counts = $eventsLog->countEvents([$type], null, null, null, null, 'count', 'desc', $limit, 'userId')) {
            foreach ($counts as $userId => $count) {
                if ($userElement = $this->getUserElement($userId)) {
                    $data[] = ['user' => $userElement, 'count' => $count];
                }
            }
        }

        return $data;
    }

    /**
     * @return array[]
     *
     * @psalm-return list{0?: array{user: mixed, count: mixed},...}
     */
    public function getTopVotesUsers($limit = 10): array
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
                if ($userElement = $this->getUserElement($row['userId'])) {
                    $data[] = ['user' => $userElement, 'count' => $row['count(id)']];
                }
            }
        }
        return $data;
    }

    public function getUserElement($userId)
    {
        return $this->getService('structureManager')->getElementById($userId, null, true);
    }
}

