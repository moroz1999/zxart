<?php

use App\Logging\EventsLog;

class visitorsElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    public $pager;
    public $visitorsList;
    protected $visitorManager;
    protected $structureManager;
    protected $eventsLog;
    protected $requestedVisitor;
    const DEFAULT_ORDER_COLUMN = 'lastVisitTime';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['elementType'] = 'text';
        $moduleStructure['elementId'] = 'text';
        $moduleStructure['elementName'] = 'text';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['userIP'] = 'text';
        $moduleStructure['periodStart'] = 'text';
        $moduleStructure['periodEnd'] = 'text';
        $moduleStructure['action'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    protected function getTabsList()
    {
        return [
            'show',
            'showForm',
            'showPrivileges',
        ];
    }

    public function getContentListDirection()
    {
        return $this->getService(controller::class)
            ->getParameter('dir') === 'asc' ? 'asc' : 'desc';
    }

    public function getVisitorDetails($requestedVisitor)
    {
        $visitor = new Visitor();
        return $visitor->getVisitor($requestedVisitor);
    }

    public function getContentListOrder()
    {
        return $this->getService(controller::class)
            ->getParameter('order') ?: self::DEFAULT_ORDER_COLUMN;
    }

    public function getOrderLinkHref($parameter)
    {
        $params = $this->getService(controller::class)->getParameters();
        unset($params['page']);
        unset($params['dir']);
        $params['order'] = $parameter;
        if ($this->getContentListOrder() == $parameter && $this->getContentListDirection() == 'desc') {
            $params['dir'] = 'asc';
        }
        return $this->URL . '?' . http_build_query($params);
    }

    public function loadVisitors()
    {
        $db = $this->getService('db');
        $db->enableQueryLog();
        $visitorsManager = $this->getService(VisitorsManager::class);
        $eventsLog = $this->getService(EventsLog::class);
        $query = $visitorsManager->createVisitorQuery();
        $query->leftJoin($db->raw('(SELECT `visitorId`,  SUM(`amount`) ordersSum,  `orderId`,  `orderStatus` FROM
            `engine_visitor_order` LEFT JOIN engine_module_order ON orderId = engine_module_order.id WHERE 
            orderStatus = \'payed\' OR orderStatus = \'paid_partial\' OR orderStatus = \'sent\' GROUP BY visitorId, orderStatus) ordersums'),
            function ($join) {
                $join->on('visitor.id', '=', $this->getService('db')
                    ->raw('ordersums.visitorId'));
            });
        $query->leftJoin($db->raw('(SELECT visitorId, MAX(time) lastVisitTime FROM `engine_visit` GROUP BY visitorId) last_visits'), function (
            $join
        ) {
            $join->on('visitor.id', '=', $this->getService('db')
                ->raw('last_visits.visitorId'));
        });
        $query->leftJoin($db->raw('(SELECT visitorId, time, referer as lastReferer FROM `engine_visit`) referers'), function (
            $join
        ) {
            $join->on('visitor.id', '=', $this->getService('db')
                ->raw('referers.visitorId'));
            $join->on($this->getService('db')
                ->raw('lastVisitTime'), '=', $this->getService('statsDb')
                ->raw('referers.time'));
        });
        $query->select(
            'visitor.id'
            , 'visitor.firstName'
            , 'visitor.lastName'
            , 'visitor.email'
            , 'ordersSum'
            , 'lastVisitTime'
            , 'lastReferer'
        );
        $filters = $this->getFilters();
        if ($filters['start'] || $filters['end']) {
            $query->whereExists(function ($query) use ($filters) {
                $query->select($this->getService('db')->raw(1))
                    ->from('visit')
                    ->where('visit.visitorId', '=', 'engine_visitor.id');
                if ($filters['start']) {
                    $startTime = strtotime($filters['start'] . ' 00:00:00');
                    $query->where('time', '>=', $startTime);
                }
                if ($filters['end']) {
                    $endTime = strtotime($filters['end'] . ' 23:59:59');
                    $query->where('time', '<=', $endTime);
                }
            });
        }
        if ($filters['minOrderSum']) {
            $query->where('ordersSum', '>=', $filters['minOrderSum']);
        }
        if ($filters['firstName']) {
            $query->where('firstName', 'like', '%' . $filters['firstName'] . '%');
        }
        if ($filters['lastName']) {
            $query->where('lastName', 'like', '%' . $filters['lastName'] . '%');
        }
        if ($filters['email']) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }
        if ($filters['product']) {
            $productId = (int)$filters['product'];
            $eventTypeId = $eventsLog->getEventTypeId('product_view');
            $query->whereExists(function ($query) use ($productId, $eventTypeId, $db) {
                $query->select($db->raw(1))
                    ->from('event')
                    ->where([
                        ['event.visitorId', '=', 'engine_visitor.id'],
                        ['event.typeId', '=', $eventTypeId],
                        ['event.elementId', '=', $productId],
                    ]);
            });
        }
        if ($filters['category']) {
            $categoryId = (int)$filters['category'];
            $eventTypeId = $eventsLog->getEventTypeId('category_view');
            $query->whereExists(function ($query) use ($categoryId, $eventTypeId, $db) {
                $query->select($db->raw(1))
                    ->from('event')
                    ->where([
                        ['event.visitorId', '=', 'engine_visitor.id'],
                        ['event.typeId', '=', $eventTypeId],
                        ['event.elementId', '=', $categoryId],
                    ]);
            });
        }

        $pagerURL = $this->URL;
        $elementsOnPage = 25;
        $elementsCount = $query->count();

        $page = 0;
        $controller = controller::getInstance();
        if ($controller->getParameter('page')) {
            $page = intval($controller->getParameter('page'));
        }
        $pager = $this->pager = new pager($pagerURL, $elementsCount, $elementsOnPage, $page, 'page');
        $query
            ->skip($pager->startElement)
            ->take($elementsOnPage);
        $order = $this->getContentListOrder();
        $query->orderBy($order, $this->getContentListDirection());

        $result = $query->get();
        $this->visitorsList = $result;
    }

    public function getFilters()
    {
        $defaults = array_map(function () {
            return '';
        }, array_flip([
            'start',
            'end',
            'firstName',
            'lastName',
            'email',
            'category',
            'product',
            'minOrderSum',
        ]));
        $params = (array)$this->getService(controller::class)->getParameters();
        array_walk($params, function (&$value, $key) {
            $value = trim($value);
        });
        $params = array_filter($params);
        return $params + $defaults;
    }
}