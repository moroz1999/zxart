<?php

namespace App\Logging;

use App\Users\CurrentUser;
use DependencyInjectionContextInterface;
use DependencyInjectionContextTrait;
use errorLogger;
use Event;
use Illuminate\Database\MySqlConnection;
use persistableCollection;
use ServerSessionManager;
use VisitorsManager;

class EventsLog extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;

    const EVENT_TABLE = 'event';
    const EVENTTYPE_TABLE = 'eventtype';
    const EVENT_STATS_DAYS_TABLE = 'events_stats_day';

    public function __construct(
        protected MySqlConnection $db,
        protected MySqlConnection $statsDb,
        protected VisitorsManager $visitorsManager,
        protected ServerSessionManager $serverSessionManager,
        protected CurrentUser $user
    )
    {
    }

    public function countEvents(
        $types,
        $elementIds = null,
        $userIds = null,
        $startTime = null,
        $endTime = null,
        $orderBy = null,
        $orderDestination = 'asc',
        $limit = null,
        $dateGroup = false,
    ): array
    {
        if ($orderBy === 'time') {
            $orderBy = 'day';
        }

        $counts = [];
        $collection = persistableCollection::getInstance(self::EVENT_STATS_DAYS_TABLE);

        $typeIds = [];
        foreach ($types as $type) {
            if ($typeId = $this->getTypeId($type)) {
                $typeIds[] = $typeId;
            }
        }

        if ($dateGroup === 'day') {
            $columns = ['sum(amount) AS count, DATE_FORMAT(day,"%d.%m.%Y") AS formattedDay'];
        } elseif ($dateGroup === 'userId') {
            $columns = ['sum(amount) AS count, target'];
        } else {
            $columns = ['sum(amount) AS count'];
        }

        $conditions = [];
        if ($userIds !== null) {
            $conditions[] = ['column' => 'target', 'action' => 'in', 'argument' => $userIds];
        }
        if ($elementIds !== null) {
            $conditions[] = ['column' => 'target', 'action' => 'in', 'argument' => $elementIds];
        }
        $conditions[] = ['column' => 'typeId', 'action' => 'in', 'argument' => $typeIds];
        if ($startTime !== null) {
            $conditions[] = [
                'column' => 'day',
                'action' => '>=',
                'argument' => 'FROM_UNIXTIME(' . $startTime . ',"%Y-%m-%d")',
                true,
            ];
        }
        if ($endTime !== null) {
            $conditions[] = [
                'column' => 'day',
                'action' => '<=',
                'argument' => 'FROM_UNIXTIME(' . $endTime . ',"%Y-%m-%d")',
                true,
            ];
        }

        if ($orderBy !== null) {
            $orderFields = [
                $orderBy => $orderDestination,
            ];
        } else {
            $orderFields = [];
        }

        if ($dateGroup === 'day') {
            $group = ['day'];
        } elseif ($dateGroup === 'userId') {
            $group = ['target'];
        } else {
            $group = [];
        }

        if ($result = $collection->conditionalLoad($columns, $conditions, $orderFields, $limit, $group, true)) {
            if ($dateGroup === 'day') {
                foreach ($result as &$row) {
                    $counts[$row['formattedDay']] = $row['count'];
                }
            } elseif ($dateGroup === 'userId') {
                foreach ($result as $row) {
                    $counts[$row['target']] = $row['count'];
                }
            } else {
                if ($row = reset($result)) {
                    $counts = $row['count'];
                }
            }
        }

        return $counts;
    }

    public function logEvent($elementId, $type, $userId = null, $targetId = false, $uri = false)
    {
        if ($userId === null) {
            $user = $this->user;
            if ($user->userName !== 'anonymous') {
                $userId = $user->id;
            } else {
                $userId = 0;
            }
        }
        $collection = persistableCollection::getInstance('events_log');

        $object = $collection->getEmptyObject();
        $object->type = $type;
        $object->userIp = $_SERVER['REMOTE_ADDR'];
        $object->userId = $userId;
        $object->session = $this->serverSessionManager->getSessionId();
        $object->time = time();
        $object->elementId = $elementId;
        $object->targetId = $targetId;
        if ($uri) {
            $object->uri = $uri;
        }

        $object->persist();
    }

    public function saveEvent(Event $event)
    {
        // TODO parameters storing (eg for newsletter events)
        $eventId = $this->getEventTypeId($event->getType());
        if (!$eventId) {
            $eventId = $this->addEventTypeAndGetId($event->getType());
        }
        if ($event->getType() === 'newsMail_linkClicked' || $event->getType() === 'newsMail_externalLinkClicked') {
            $this->checkVisitorOpenedEmail($event->getVisitorId(), $event->getElementId());
        }
        $data['typeId'] = $eventId;
        $data['visitorId'] = $event->getVisitorId();
        $data['elementId'] = $event->getElementId();
        $data['time'] = $event->getTime();
        return $this->createEventQuery()->insertGetId($data);
    }

    public function generateEvent($type, $elementId = 0, array $parameters = [])
    {
        $event = new Event();
        $event->setType($type);
        $event->setTime(time());
        $event->setParameters($parameters);
        $visitor = $this->visitorsManager->getCurrentVisitor();
        $event->setVisitorId($visitor ? $visitor->id : 0);
        $event->setElementId($elementId);
        return $event;
    }

    public function deleteEventsByType($type, $time = 0)
    {
        $typeId = $this->getEventTypeId($type);
        $query = $this->createEventQuery()
            ->where('typeId', '=', $typeId);
        if ($time > 0) {
            $query->where('time', '<', $time);
        }
        return $query->delete();
    }

    public function updateVisitor($fromId, $toId)
    {
        $this->createEventQuery()
            ->where('visitorId', $fromId)
            ->update(['visitorId' => $toId]);
    }

    public function queryElementEventOccurrences(
        $eventType,
        $visitorId,
        $limit = 0,
    )
    {
        $query = $this->createEventQuery()
            ->select($this->statsDb->raw('elementId, count(1) as occurrences'))
            ->where('typeId', $this->getEventTypeId($eventType))
            ->where('visitorId', $visitorId)
            ->groupBy('elementId')
            ->orderBy('occurrences', 'desc');
        if ($limit) {
            $query->limit($limit);
        }
        $result = $query->pluck('occurrences', 'elementId');
        return $result;
    }

    public function createEventQuery()
    {
        return $this->statsDb->table(self::EVENT_TABLE);
    }

    public function getEventTypeId($type)
    {
        return (int)$this->statsDb
            ->table(self::EVENTTYPE_TABLE)
            ->where('type', $type)
            ->value('id');
    }

    protected function addEventTypeAndGetId($type)
    {
        return (int)$this->statsDb->table(self::EVENTTYPE_TABLE)
            ->insertGetId(['type' => $type]);
    }


    public function deleteEvents($type, $time = false)
    {
        $query = $this->db->table('events_log')->where('type', '=', $type);
        if ($time) {
            $query->where('time', '<', $time);
        }
        return $query->delete();
    }

    public function aggregateEvents($type, $time, $groupField = 'elementId')
    {
        if ($typeId = $this->checkTypeId($type)) {
            $this->db->statement('
            INSERT INTO `engine_' . self::EVENT_STATS_DAYS_TABLE . '` (target, typeId, day, amount)
            SELECT ' . $groupField . ', ?,  DATE_FORMAT(FROM_UNIXTIME(`time`), "%Y-%m-%d") AS day, count(*) AS amount FROM `engine_events_log` WHERE `type`=? AND `time`<? GROUP BY ' . $groupField . ', day ;', [
                $typeId,
                $type,
                $time,
            ]);
        }
    }

    protected function checkTypeId($type)
    {
        if ($typeId = $this->getTypeId($type)) {
            return $typeId;
        } else {
            return $this->db->table('events_stats_types')->insertGetId(['name' => $type]);
        }
    }

    protected function getTypeId($type)
    {
        if ($record = $this->db->table('events_stats_types')->select('id')->where('name', '=', $type)->first()) {
            return $record['id'];
        }
        return false;
    }

    protected function checkVisitorOpenedEmail($visitorId, $newsMailId)
    {
        $eventsName = ['newsMail_emailOpened', 'newsMail_viewFromBrowser'];
        $eventsId = $this->db->table('eventtype')->select('id')->whereIn('type', $eventsName)->get();
        $events = $this->db->table('event')->select('id')
            ->whereIn('typeId', $eventsId)
            ->where('visitorId', '=', $visitorId)
            ->where('elementId', '=', $newsMailId)
            ->get();
        if (empty($events)) {
            $event = $this->generateEvent('newsMail_emailOpened', $newsMailId, []);
            $this->saveEvent($event);
        }
    }
}