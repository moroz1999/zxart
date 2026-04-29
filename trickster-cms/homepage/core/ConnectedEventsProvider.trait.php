<?php

/**
 * Class ConnectedEventsProviderTrait
 *
 * Functionality for manually connecting events in admin panel to structure elements
 */
trait ConnectedEventsProviderTrait
{
    protected $connectedEventsIds;
    protected $connectedEvents;
    protected $connectedEventsListsIds;
    protected $connectedEventsLists;

    public function getConnectedEvents()
    {
        if (is_null($this->connectedEvents)) {
            $this->connectedEvents = array();
            if ($eventsIds = $this->getConnectedEventsIds()) {
                $structureManager = $this->getService('structureManager');
                foreach ($eventsIds as &$eventId) {
                    if ($eventId && $eventElement = $structureManager->getElementById($eventId)) {
                        $this->connectedEvents[] = $eventElement;
                    }
                }
            }
        }
        return $this->connectedEvents;
    }

    public function getConnectedEventsLists()
    {
        if (is_null($this->connectedEventsLists)) {
            $this->connectedEventsLists = [];
            if ($eventsListsIds = $this->getConnectedEventsListsIds()) {
                $structureManager = $this->getService('structureManager');
                foreach ($eventsListsIds as $eventsListId) {
                    if ($eventsListId && $eventsListElement = $structureManager->getElementById($eventsListId)) {
                        $this->connectedEventsLists[] = $eventsListElement;
                    }
                }
            }
        }
        return $this->connectedEventsLists;
    }

    public function getConnectedEventsListsIds()
    {
        if ($this->connectedEventsListsIds === null) {
            $this->connectedEventsListsIds = $this->getService(linksManager::class)
                ->getConnectedIdList($this->id, $this->getLinkType(true), 'parent');
        }
        return $this->connectedEventsListsIds;
    }

    public function getConnectedEventsIds()
    {
        if ($this->connectedEventsIds === null) {
            $this->connectedEventsIds = $this->getService(linksManager::class)
                ->getConnectedIdList($this->id, $this->getLinkType(), "parent");
        }
        return $this->connectedEventsIds;
    }

    public function connectFormEvents()
    {
        // connect events
        $linksManager = $this->getService(linksManager::class);
        if ($connectedIds = $this->getConnectedEventsIds()) {
            foreach ($connectedIds as &$connectedId) {
                if (!in_array($connectedId, $this->receivedEventsIds)) {
                    $linksManager->unLinkElements($this->id, $connectedId, $this->getLinkType());
                }
            }
        }
        foreach ($this->receivedEventsIds as $receivedEventId) {
            $linksManager->linkElements($this->id, $receivedEventId, $this->getLinkType());
        }
    }

    public function connectFormEventsLists()
    {
        $linksManager = $this->getService(linksManager::class);
        if ($connectedIds = $this->getConnectedEventsListsIds()) {
            foreach ($connectedIds as &$connectedId) {
                if (!in_array($connectedId, $this->receivedEventsListsIds)) {
                    $linksManager->unLinkElements($this->id, $connectedId, $this->getLinkType(true));
                }
            }
        }
        foreach ($this->receivedEventsListsIds as $receivedEventsListId) {
            $linksManager->linkElements($this->id, $receivedEventsListId, $this->getLinkType(true));
        }
    }

    protected function getLinkType($eventsList = false)
    {
        if (!$eventsList) {
            return $this->structureType . 'Event';
        } else {
            return $this->structureType . 'EventsList';
        }
    }
}