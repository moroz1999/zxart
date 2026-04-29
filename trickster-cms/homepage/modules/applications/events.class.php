<?php

use App\Logging\EventsLog;

class eventsApplication extends controllerApplication {
    protected $eventId;
    public function initialize()
    {
//        set_time_limit(60 * 60);
//        $this->startSession('events');
//        $this->createRenderer();
    }
    public function execute($controller)
    {
        $visitorManager = $this->getService(VisitorsManager::class);
        $eventLogger = $this->getService(EventsLog::class);
        $currentVisitor = $visitorManager->getCurrentVisitor();
        $action = $controller->getParameter('action');
        if($action == 'emailClick') {
            $event = new Event();
            $event->setElementId(0);
            $event->setType('emailClick');
            $event->setVisitorId($currentVisitor->id);
            $this->eventId = $eventLogger->saveEvent($event);
            $email = $controller->getParameter('email');
            $this->saveConnectedEmailAddress($email);
        }
    }

    protected function saveConnectedEmailAddress($email) {
        $db = $this->getService('db');
        if (!empty($this->eventId)) {
            $uriId = $db->table('visitor_uri')
                ->where('visitor_uri.uri', '=', $email)
                ->select('visitor_uri.id')
                ->first();
            if (empty($uriId)) {
                $uriId['id'] = $db->table('visitor_uri')
                    ->insertGetId(
                        ['uri' => $email]
                    );
            }
            $db->table('link_event_uri')
                ->insertGetId(
                    ['eventId' => $this->eventId, 'uriId' => $uriId['id']]
                );
        }
    }
};