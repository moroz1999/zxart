<?php

use App\Users\CurrentUserService;

class VisitorRemoveApplication extends controllerApplication
{
    protected $applicationName = 'VisitorRemove';
    protected $latestVisitors = [];

    public function initialize()
    {
        set_time_limit(1 * 60);
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable(false, false, true);

        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);
            $structureManager = $this->getService('adminStructureManager');

            $timestamp = strtotime(date("d-m-Y", strtotime("-3 months")));
            $visitors = $this->getVisitorsOlderThan($timestamp);
            if (!empty($visitors)) {
                $db = $this->getService('db');
                foreach ($visitors as $visitor) {
                    $result = $db->table('visitor_order')
                        ->leftJoin('module_order', 'visitor_order.orderId', '=', 'module_order.id')
                        ->where('visitor_order.visitorId', '=', $visitor['visitorId'])
                        ->whereIn('module_order.orderStatus', ['payed', 'paid_partial', 'sent'])
                        ->select('visitor_order.visitorId')
                        ->get();
                    if(empty($result)) {
                        $this->deleteVisitor($visitor['visitorId']);
                    }
                }
            }
        }
    }

    protected function getVisitorsOlderThan($timestamp) {
        $this->latestVisitors();
        $db = $this->getService('db');
        $result = $db->table('visit')
            ->where('time', '<=', $timestamp)
            ->whereNotIn('visitorId', $this->latestVisitors)
            ->select('visitorId', 'time')
            ->distinct()
            ->get();
        return $result;
    }

    protected function getVisitorEvents($visitorId) {
        $db = $this->getService('db');
        $result = $db->table('event')->where('visitorId', '=', $visitorId)->get();
        return $result;
    }

    protected function deleteVisitor($visitorId) {
        $db = $this->getService('db');
        $events = $this->getVisitorEvents($visitorId);
        foreach ($events as $event) {
            $db->table('event')->where('id', '=', $event['id'])->delete();
            $db->table('link_event_uri')->where('eventId', '=', $event['id'])->delete();
        }
        $db->table('search_log')->where('visitorId', '=', $event['visitorId'])->delete();
        $db->table('visit')->where('visitorId', '=', $event['visitorId'])->delete();
        $db->table('visitor')->where('id', '=', $event['visitorId'])->delete();
        $db->table('visitor')->where('id', '=', $event['visitorId'])->delete();
    }

    protected function latestVisitors() {
        $db = $this->getService('db');
        $timestamp = strtotime(date("d-m-Y", strtotime("-3 months")));
        $result = $db->table('visit')
            ->where('time', '>=', $timestamp)
            ->where('visitorId', '>', 0)
            ->select('visitorId')
            ->distinct()
            ->get();
        $visitors = [];
        foreach ($result as $visitor) {
            array_push($this->latestVisitors, $visitor['visitorId']);
        }
        return $visitors;
    }
}



