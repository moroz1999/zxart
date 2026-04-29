<?php

use App\Logging\EventsLog;

trait EventLoggingElementTrait
{
    public function logViewEvent()
    {
        $this->logVisitorEvent($this->structureType . '_view');
    }

    public function logVisitorEvent($type, array $parameters = [])
    {
        $visitorsManger = $this->getService(VisitorsManager::class);
        $visitor = $visitorsManger->getCurrentVisitor();
        if (!$visitor) {
            return;
        }
        $eventsLog = $this->getService(EventsLog::class);
        $event = $eventsLog->generateEvent($type, $this->id, $parameters);
        $eventsLog->saveEvent($event);
    }
}