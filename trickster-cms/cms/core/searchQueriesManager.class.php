<?php

use App\Users\CurrentUserService;

class searchQueriesManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    /** @var searchQueriesManager */
    private static $instance;

    /**
     * @return searchQueriesManager
     * @deprecated
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new searchQueriesManager();
        }
        return self::$instance;
    }

    public function __construct()
    {
        self::$instance = $this;
        $this->defaultRoles = [
            "content",
            "container",
        ];
        $this->dataCollection = persistableCollection::getInstance("search_log");
    }

    /** Adds a new search log
     * @param string $phrase
     * @param int $resultsCount
     * @return int
     */
    public function logSearch($phrase, $resultsCount)
    {
        $visitorManager = $this->getService(VisitorsManager::class);
        if ($currentVisitor = $visitorManager->getCurrentVisitor()) {
            $record = $this->dataCollection->getEmptyObject();

            $record->phrase = $phrase;
            $record->resultsCount = $resultsCount;
            $record->date = time();
            $record->bClicked = 0;
            $record->visitorId = $currentVisitor->id;
            $record->persist();
            return $record->id;
        }
        return false;
    }

    /** Adds a new log when necessary, otherwise updates an old log. (Meant for usage with Ajax search)
     * @param string $phrase
     * @param int $resultsCount
     * @return bool|int
     */
    public function logInstantSearch($phrase, $resultsCount)
    {
        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        $lastSearchPhrase = $user->getStorageAttribute("lastSearchPhrase");
        if ($lastSearchPhrase && stripos($phrase, $lastSearchPhrase) !== false) {
            $searchId = $user->getStorageAttribute("lastSearchId");
            $this->updateSearchLog($searchId, $phrase, $resultsCount);
            $user->setStorageAttribute("lastSearchPhrase", $phrase);
        } else {
            $searchId = $this->logSearch($phrase, $resultsCount);
            $user->setStorageAttribute("lastSearchId", $searchId);
            $user->setStorageAttribute("lastSearchPhrase", $phrase);
        }
        return $searchId;
    }

    /** Updates an existing search log
     * @param int $id - ID of the log line we're updating
     * @param string $phrase
     * @param int $resultsCount
     */
    public function updateSearchLog($id, $phrase, $resultsCount)
    {
        $visitorManager = $this->getService(VisitorsManager::class);
        $currentVisitor = $visitorManager->getCurrentVisitor();
        $queryDataObjects = $this->dataCollection->load(["id" => $id]);
        if ($queryDataObjects) {
            $queryDataObjects[0]->phrase = $phrase;
            $queryDataObjects[0]->resultsCount = $resultsCount;
            $queryDataObjects[0]->date = time();
            $queryDataObjects[0]->bClicked = 0;
            $queryDataObjects[0]->visitorId = $currentVisitor->id;
            $queryDataObjects[0]->persist();
        }
    }

    public function markLogAsClicked($id)
    {
        $queryDataObjects = $this->dataCollection->load([
            "id" => $id,
            "bClicked" => 0,
        ]);
        if ($queryDataObjects) {
            $queryDataObjects[0]->bClicked = 1;
            $queryDataObjects[0]->persist();
        }
    }
}





