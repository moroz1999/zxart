<?php

class EmailDispatcher extends errorLogger
{
    protected $timeLimit;
    protected $oneDispatchmentDelay;
    protected $designThemesManager;

    public function __construct()
    {
        $this->timeLimit = 10;
        $this->oneDispatchmentDelay = 1;
    }

    /**
     * @param mixed $designThemesManager
     */
    public function setDesignThemesManager($designThemesManager)
    {
        $this->designThemesManager = $designThemesManager;
    }

    /**
     * @return DesignThemesManager
     */
    public function getDesignThemesManager()
    {
        if (!$this->designThemesManager) {
            $controller = controller::getInstance();
            //only for possible backwards compatibility, use DI instead!
            $configManager = $controller->getConfigManager();
            $pathsManager = $controller->getPathsManager();
            $this->designThemesManager = new DesignThemesManager();
            $themesPath = $pathsManager->getRelativePath('themes');
            foreach ($pathsManager->getIncludePaths() as $path) {
                $this->designThemesManager->setThemesDirectoryPath($path . $themesPath);
            }
            $this->designThemesManager->setCurrentThemeCode($configManager->get('main.publicTheme'));
        }
        return $this->designThemesManager;
    }

    public function setTimeLimit($timeLimit)
    {
        $this->timeLimit = $timeLimit;
    }

    public function setOneDispatchmentDelay($oneDispatchmentDelay)
    {
        $this->oneDispatchmentDelay = $oneDispatchmentDelay;
    }

    /**
     *
     */
    public function dispatchAwaitingList()
    {
        $startTime = time();
        if ($historyList = $this->getDispatchmentsToSend()) {
            foreach ($historyList as &$item) {
                if ($dispatchment = $this->getDispatchment($item['dispatchmentId'])) {
                    $currentTime = time();
                    while (($currentTime <= $startTime + $this->timeLimit) && $dispatchment->dispatchAwaitingItem()) {
                        if ($this->oneDispatchmentDelay) {
                            sleep($this->oneDispatchmentDelay);
                        }
                        $currentTime = time();
                    }
                }
            }
        }
    }

    protected function getDispatchmentsToSend()
    {
        $history = [];
        $collection = persistableCollection::getInstance('email_dispatchments_history');

        $conditions = [
            [
                'column' => 'status',
                'action' => '=',
                'argument' => 'awaiting',
            ],
            [
                'column' => 'startTime',
                'action' => '<=',
                'argument' => time(),
            ],
        ];
        $order = [
            'priority' => 'asc',
            'startTime' => 'asc',
        ];
        if ($rows = $collection->conditionalLoad(null, $conditions, $order)) {
            foreach ($rows as &$row) {
                if ($row['referenceId'] > 0) {
                    $history[$row['referenceId']] = $row;
                }
            }
        }

        return $history;
    }

    /**
     * @param EmailDispatchment $emailDispatchment
     * @return bool
     */
    public function startDispatchment($emailDispatchment)
    {
        $result = false;
        $emailDispatchment->persist();
        $startTime = time();
        $currentTime = $startTime;
        while ($currentTime <= $startTime + $this->timeLimit && $status = $emailDispatchment->dispatchAwaitingItem()) {
            if ($status == 'success') {
                $result = true;
            }
            $currentTime = time();
        }
        return $result;
    }

    /**
     * @param EmailDispatchment $emailDispatchment
     */
    public function appointDispatchment($emailDispatchment)
    {
        $emailDispatchment->persist();
    }

    public function getReferencedDispatchmentHistory($referenceId)
    {
        $history = false;
        if ($dispatchments = $this->loadReferencedDispatchments($referenceId)) {
            $history = $this->getAggregatedDispatchmentsHistory($dispatchments);
        }
        return $history;
    }

    /**
     * @param int $referenceId
     * @return EmailDispatchment[]
     */
    protected function loadReferencedDispatchments($referenceId)
    {
        $dispatchments = [];

        $collection = persistableCollection::getInstance('email_dispatchments');
        $conditions = [
            [
                'column' => 'referenceId',
                'action' => '=',
                'argument' => $referenceId,
            ],
        ];
        if ($rows = $collection->conditionalLoad('id', $conditions, ['startTime' => 'asc'])) {
            foreach ($rows as &$row) {
                if ($dispatchment = $this->getDispatchment($row['id'])) {
                    $dispatchments[] = $dispatchment;
                }
            }
        }

        return $dispatchments;
    }

    /**
     * @param EmailDispatchment[] $dispatchments
     * @return array
     */
    protected function getAggregatedDispatchmentsHistory($dispatchments)
    {
        $history = [];
        $collection = persistableCollection::getInstance('email_dispatchments_history');

        foreach ($dispatchments as &$dispatchment) {
            $dispatchmentId = $dispatchment->getId();
            $conditions = [
                [
                    'column' => 'dispatchmentId',
                    'action' => '=',
                    'argument' => $dispatchmentId,
                ],
            ];
            if ($rows = $collection->conditionalLoad(null, $conditions, ['startTime' => 'asc'])) {
                foreach ($rows as &$row) {
                    if ($row['referenceId'] > 0) {
                        $history[$row['referenceId']] = $row;
                    }
                }
            }
        }
        return $history;
    }

    public function cancelReferencedDispatchments($referenceId)
    {
        if ($dispatchments = $this->loadReferencedDispatchments($referenceId)) {
            foreach ($dispatchments as &$dispatchment) {
                $dispatchment->cancelSending();
            }
        }
    }

    public function getDispatchment($dispatchmentId)
    {
        $result = false;
        $collection = persistableCollection::getInstance('email_dispatchments');
        if ($objects = $collection->load(['id' => $dispatchmentId])) {
            $data = reset($objects);
            $dispatchment = $this->getEmptyDispatchment();
            $dispatchment->setPersistedData($data);
            $result = $dispatchment;
        }
        return $result;
    }

    public function getEmptyDispatchment()
    {
        $emailDispatchment = new EmailDispatchment();
        $emailDispatchment->setEmailDispatcher($this);
        return $emailDispatchment;
    }

    public function clearOutdatedDispatchmentsData()
    {
        $collection = persistableCollection::getInstance('email_dispatchments');

        $conditions = [
            [
                'column' => 'dataLifeTime + startTime',
                'action' => '<',
                'argument' => time(),
                'literal' => true,
            ],
            [
                'column' => 'data',
                'action' => '!=',
                'argument' => '',
            ],
        ];

        if ($rows = $collection->conditionalLoad(['id'], $conditions)) {
            foreach ($rows as &$row) {
                if ($dispatchment = $this->getDispatchment($row['id'])) {
                    $dispatchment->clearData();
                }
            }
        }
    }
}