<?php

class votesManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    protected $overallAverageVote;
    protected $elementAverageVotes = [];
    protected $elementVotesCounts = [];
    protected $elementVotesList = [];
    /** @var votesManager */
    private static $instance;
    protected $elementsTypesIndex = [];

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new votesManager();
        }
        return self::$instance;
    }

    public function getOverallAverageVote()
    {
        if (is_null($this->overallAverageVote)) {
            $this->overallAverageVote = $this->getService('ConfigManager')->get('zx.averageVote');
        }
        return $this->overallAverageVote;
    }

    public function getElementAverageVote($elementId)
    {
        if (!isset($this->elementAverageVotes[$elementId])) {
            $this->elementAverageVotes[$elementId] = 0;
            $db = $this->getService('db');
            if ($average = $db->table('votes_history')
                ->where('elementId', '=', $elementId)
                ->where('value', '!=', 0)
                ->avg('value')
            ) {
                if (is_numeric($average)) {
                    $this->elementAverageVotes[$elementId] = $average;
                }
            }
        }
        return $this->elementAverageVotes[$elementId];
    }

    public function getElementVotesCount($elementId)
    {
        if (!isset($this->elementVotesCounts[$elementId])) {
            $this->elementVotesCounts[$elementId] = 0;

            $db = $this->getService('db');
            if ($count = $db->table('votes_history')
                ->where('elementId', '=', $elementId)
                ->where('value', '!=', 0)
                ->count('value')
            ) {
                if (is_numeric($count)) {
                    $this->elementVotesCounts[$elementId] = $count;
                }
            }
        }
        return $this->elementVotesCounts[$elementId];
    }

    public function getElementFilteredVotes($elementId): array
    {
        $votesList = $this->getElementVotesList($elementId);
        $votes = array_column($votesList, 'value');
        $analyzer = new VoteAnalyzer();
        $filteredVotes = $analyzer->removeAnomalies($votes);
        return $filteredVotes;
    }

    public function getElementVotesList($elementId)
    {
        if (!isset($this->elementVotesList[$elementId])) {
            $this->elementVotesList[$elementId] = [];

            $db = $this->getService('db');
            if ($votes = $db->table('votes_history')
                ->where('elementId', '=', $elementId)
                ->where('value', '!=', 0)
                ->orderBy('date', 'desc')
                ->get()
            ) {
                $this->elementVotesList[$elementId] = $votes;
            }
        }
        return $this->elementVotesList[$elementId];
    }

    public function getElementUserVote($id, $type)
    {
        if (!isset($this->elementsTypesIndex[$type])) {
            $this->elementsTypesIndex[$type] = [];
        }

        if (!isset($this->elementsTypesIndex[$type][$id])) {
            if ($elements = $this->getService('structureManager')->getLoadedElementsByType($type)) {
                $idList = [];
                foreach ($elements as $element) {
                    if (!isset($this->elementsTypesIndex[$type][$element->id])) {
                        $idList[] = $element->id;
                        $this->elementsTypesIndex[$type][$element->id] = false;
                    }
                }

                if ($records = $this->loadVotesByIdList($idList)) {
                    foreach ($records as $row) {
                        $this->elementsTypesIndex[$type][$row['elementId']] = $row['value'];
                    }
                }
            }
        }

        return $this->elementsTypesIndex[$type][$id];
    }

    /**
     * @psalm-param list{0?: mixed,...} $idList
     */
    protected function loadVotesByIdList(array $idList)
    {
        $records = false;
        if ($idList) {
            $collection = persistableCollection::getInstance('votes_history');
            $user = $this->getService('user');

            $columns = ['elementId', 'value'];

            $conditions = [];
            $conditions[] = ['column' => 'userId', 'action' => '=', 'argument' => $user->id];
            $conditions[] = ['column' => 'elementId', 'action' => 'in', 'argument' => $idList];

            $records = $collection->conditionalLoad($columns, $conditions);
        }
        return $records;
    }

    /**
     * @psalm-param int<min, max> $value
     *
     * @return true
     */
    public function vote(int $elementId, string $type, int $value): bool
    {
        $collection = persistableCollection::getInstance('votes_history');
        $user = $this->getService('user');
        $result = $collection->load(['userId' => $user->id, 'elementId' => $elementId]);
        $historyObject = false;
        if (count($result) > 0) {
            $historyObject = reset($result);
        }

        if (!$historyObject) {
            $historyObject = $collection->getEmptyObject();
            $historyObject->userId = $user->id;
            $historyObject->ip = $user->IP;
            $historyObject->type = $type;
            $historyObject->elementId = $elementId;
        }

        $historyObject->date = time();
        $historyObject->value = $value;
        $historyObject->persist();
        return true;
    }

    /**
     * @psalm-return array{0?: mixed,...}|false
     */
    public function getLatestVotes($limit, $types = null): array|false
    {
        $votes = false;
        $query = $this->getService('db')->table('votes_history')->select('*');
        if ($types) {
            $query->whereIn('type', $types);
        }
        $query->orderBy('id', 'desc')->limit($limit);

        if ($votesList = $query->get()) {
            $votes = [];
            $structureManager = $this->getService('structureManager');
            foreach ($votesList as $key => $vote) {
                if (($artItemElement = $structureManager->getElementById(
                        $vote['elementId']
                    )) && !$artItemElement->isVotingDenied()
                ) {
                    if ($user = $structureManager->getElementById($vote['userId'], null, true)) {
                        $vote['userName'] = $user->userName;
                        $vote['userUrl'] = $user->getUrl();
                        $vote['userType'] = $user->getBadgeTypesString();
                    } else {
                        $vote['userName'] = '';
                        $vote['userUrl'] = '';
                        $vote['userType'] = '';
                    }
                    if ($vote['value'] == 0) {
                        $vote['value'] = 'x';
                    }
                    $vote['imageUrl'] = $artItemElement->getUrl();
                    $vote['imageTitle'] = $artItemElement->title;

                    $vote['date'] = date('d.m.Y h:i', $vote['date']);
                    $votes[] = $vote;
                } else {
                    unset($votes[$key]);
                }
            }
        }
        return $votes;
    }
}
