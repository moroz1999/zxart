<?php

use App\Logging\EventsLog;

class Visitor
{
    use DependencyInjectionContextTrait;

    public $id = 0;
    public $trackingCode;
    public $userId = 0;
    public $email = '';
    public $firstName = '';
    public $lastName = '';
    public $phone = '';
    public $user;

    protected $emailId = 0;
    protected $visits;
    protected $mostViewedCategories;
    protected $mostViewedProducts;
    protected $orders;
    protected $subscribes;
    protected $newsMailsEvents;
    protected $addedProducts;
    protected $visitorUpdated = false;
    protected $feedbacks;
    protected $emailClicks;
    protected $searchQueries = [];


    protected function setVisitorInfo()
    {
        $visitorManager = $this->getService(VisitorsManager::class);
        $query = $visitorManager->createVisitorQuery();
        $record = $query->where('id', $this->id)->first();
        if (!empty($record)) {
            $this->setData($record);
            if ($this->userId == 0) {
                $this->userId = $this->findUserId();
            }
            if ($this->userId != 0) {
                $structureManager = $this->getService('structureManager');
                $user = $structureManager->getElementById($this->userId);
                $this->setUser($user);
            }
            if (!$this->visitorUpdated) {
                $visitorManager->updateVisitor($this);
                $this->visitorUpdated = true;
                $this->setVisitorInfo();
            }
        }
    }

    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getVisitor($id)
    {
        $this->id = $id;
        $this->setVisitorInfo();
        $this->setEmailId();
        $this->setVisits();
        $this->setMostViewedCategories();
        $this->setMostViewedProducts();
        $this->setOrders();
        $this->setSubscribes();
        $this->setNewsMailsEvents();
        $this->setAddedProductsToShoppingBasket();
        $this->setFeedbackAnswers();
        $this->setEmailsClickEvent();
        $this->setSearchLog();
        return $this;
    }

    /**
     * @return array
     */
    public function getVisits()
    {
        return $this->visits;
    }

    public function setVisits()
    {
        $visitorManager = $this->getService(VisitorsManager::class);
        $result = $visitorManager->createVisitQuery()
            ->where('visitorId', $this->id)
            ->orderBy('time', 'desc')
            ->get();
        $this->visits = $result;
    }

    /**
     * @return array
     */
    public function getMostViewedCategories()
    {
        return $this->mostViewedCategories;
    }

    public function setMostViewedCategories()
    {
        $structureManager = $this->getService('structureManager');
        $eventsLog = $this->getService(EventsLog::class);
        $top = [];
        $viewsByElement = $eventsLog->queryElementEventOccurrences('category_view', $this->id);
        foreach ($viewsByElement as $id => $views) {
            $element = $structureManager->getElementById($id);
            if ($element) {
                $top[] = [
                    'title' => $element->getTitle(),
                    'url'   => $element->URL,
                    'views' => $views,
                ];
                if (count($top) == 5) {
                    break;
                }
            }
        }
        $this->mostViewedCategories = $top;
    }

    /**
     * @return array
     */
    public function getMostViewedProducts()
    {
        return $this->mostViewedProducts;
    }

    public function setMostViewedProducts()
    {
        $structureManager = $this->getService('structureManager');
        $eventsLog = $this->getService(EventsLog::class);
        $top = [];
        $viewsByElement = $eventsLog->queryElementEventOccurrences('product_view', $this->id);
        foreach ($viewsByElement as $id => $views) {
            $element = $structureManager->getElementById($id);
            if ($element) {
                $top[] = [
                    'title' => $element->getTitle(),
                    'url'   => $element->URL,
                    'views' => $views,
                ];
                if (count($top) == 10) {
                    break;
                }
            }
        }
        $this->mostViewedProducts = $top;
    }

    /**
     * @return array
     */
    public function getOrders()
    {
        return $this->orders;
    }

    public function setOrders()
    {
        $structureManager = $this->getService('structureManager');
        $visitorManager = $this->getService(VisitorsManager::class);
        $result['orders'] = [];
        $result['orderProducts'] = [];
        $result['ordersTotal'] = 0;
        $orderIds = $visitorManager->createOrderQuery()
            ->where('visitorId', $this->id)
            ->pluck('orderId');
        foreach ($orderIds as $orderId) {
            $element = $structureManager->getElementById($orderId);
            if ($element) {
                $status = $element->getOrderStatus();
                if ($status == 'payed' || $status == 'paid_partial' || $status == 'sent') {
                    $result['orders'][] = $element;
                    $result['ordersTotal'] += $element->getTotalPrice();
                    $result['orderProducts'] = array_merge($result['orderProducts'], $element->getOrderProducts());
                }
            }
        }
        if (!empty($orderId)) {
            $this->orders = $result;
        }
    }

    /**
     * @return array
     */
    public function getSubscribes()
    {
        return $this->subscribes;
    }

    public function setSubscribes()
    {
        $structureManager = $this->getService('structureManager');
        $newsLetterSubscription = null;
        if ($this->emailId) {
            $newsLetterSubscription = $structureManager->getElementById($this->emailId);
        }
        $this->subscribes = $newsLetterSubscription;
    }

    public function setEmailId()
    {
        $db = $this->getService('db');
        $id = 0;
        if (!empty($this->email)) {
            $id = $db->table('module_newsmailaddress')
                ->where('email', $this->email)
                ->value('id');
        }
        $this->emailId = $id;
    }

    /**
     * @return array
     */
    public function getNewsMailsEvents()
    {
        return $this->newsMailsEvents;
    }

    protected function getNewsMailsEventsId()
    {
        $db = $this->getService('db');
        $types = [
            'newsMail_emailOpened',
            'newsMail_externalLinkClicked',
            'newsMail_linkClicked',
            'newsMail_unsubscribe',
            'newsMail_unsubscribe_1step',
            'newsMail_viewFromBrowser'
        ];
        $eventsType = $db->table('eventtype')->whereIn('type', $types)->get();
        $eventsList = [];
        foreach ($eventsType as $event) {
            if (in_array($event['type'], $event)) {
                $eventsList[] = $event['id'];
            }
        }
        return $eventsList;
    }

    protected function setNewsMailDispatchmentsHistory()
    {
        $db = $this->getService('db');
        if ($this->emailId) {
            $rows = $db->table('email_dispatchments_history')
                ->join('email_dispatchments', 'email_dispatchments.id', '=', 'email_dispatchments_history.dispatchmentId')
                ->where('email_dispatchments_history.referenceId', '=', $this->emailId)
                ->select(
                    'email_dispatchments.referenceId',
                    'email_dispatchments_history.dispatchmentId',
                    'email_dispatchments_history.id as emailDispatchmentsHistoryId',
                    'email_dispatchments_history.status',
                    'email_dispatchments_history.priority',
                    'email_dispatchments.subject'
                )
                ->get();
            foreach ($rows as $row) {
                $this->newsMailsEvents['newsMail']['newsMails'][$row['referenceId']]['id'] = $row['emailDispatchmentsHistoryId'];
                $this->newsMailsEvents['newsMail']['newsMails'][$row['referenceId']]['name'] = $row['subject'];
                $this->newsMailsEvents['newsMail']['newsMails'][$row['referenceId']]['statistics'] = [
                    'newsMail_emailOpened'         => 0,
                    'newsMail_externalLinkClicked' => 0,
                    'newsMail_linkClicked'         => 0,
                    'newsMail_unsubscribe'         => 0,
                    'newsMail_unsubscribe_1step'   => 0,
                    'newsMail_viewFromBrowser'     => 0
                ];
                $this->newsMailsEvents['newsMail']['newsMails'][$row['referenceId']]['links'] = [];
            }
        }
    }

    protected function getVisitorEvents()
    {
        $db = $this->getService('db');
        $eventsListId = $this->getNewsMailsEventsId();
        $events = $db->table('event')
            ->join('visitor', 'visitor.id', '=', 'event.visitorId')
            ->join('eventtype', 'eventtype.Id', '=', 'event.typeId')
            ->where('visitor.id', '=', $this->id)
            ->whereIn('event.typeId', $eventsListId)
            ->select(
                'event.id as eventId',
                'type',
                'elementId'
            )
            ->get();
        return $events;
    }

    protected function getLink($id)
    {
        $db = $this->getService('db');
        $link = $db->table('link_event_uri')
            ->join('visitor_uri', 'visitor_uri.id', '=', 'link_event_uri.uriId')
            ->where('link_event_uri.eventId', '=', $id)
            ->select('visitor_uri.uri')
            ->first();
        return $link;
    }

    public function setNewsMailsEvents()
    {
        $events = $this->getVisitorEvents();
        $this->setNewsMailDispatchmentsHistory();
        if (!empty($events)) {
            foreach ($events as $event) {
                if (!isset($this->newsMailsEvents['newsMail']['totals'][$event['type']])) {
                    $this->newsMailsEvents['newsMail']['totals'][$event['type']] = 0;
                }
                $this->newsMailsEvents['newsMail']['totals'][$event['type']]++;
                if ($this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']] && !empty($this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']]['name'])) {
                    $this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']]['statistics'][$event['type']]++;
                    $this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']]['show'] = true;
                    if (!empty($event['eventId'])) {
                        $uriRows = $this->getLink($event['eventId']);
                        if (!empty($uriRows)) {
                            if (empty($this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']]['links'][$uriRows['uri']])) {
                                $this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']]['links'][$uriRows['uri']] = 1;
                            }
                            $this->newsMailsEvents['newsMail']['newsMails'][$event['elementId']]['links'][$uriRows['uri']]++;
                        }
                    }
                }
            }
        }
    }

    /**
     * @return userElement
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param userElement
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    protected function findUserId()
    {
        $db = $this->getService('db');
        $result = $db->table('module_user')
            ->where('email', '=', $this->email)
            ->orWhere('id', '=', $this->userId)
            ->select(
                'id'
            )->first();
        return $result['id'];
    }

    /**
     * @return array
     */
    public function getDataArray()
    {
        return [
            'id'           => $this->id,
            'trackingCode' => $this->trackingCode,
            'userId'       => $this->userId,
            'email'        => $this->email,
            'firstName'    => $this->firstName,
            'lastName'     => $this->lastName,
            'phone'        => $this->phone,
        ];
    }

    protected function setAddedProductsToShoppingBasket()
    {
        $this->addedProducts = [];
        $eventLogger = $this->getService(EventsLog::class);
        $eventType = 'shoppingbasket_addition';
        $db = $this->getService('db');
        $eventId = $eventLogger->getEventTypeId($eventType);
        $result = $db->table('event')
            ->where('typeId', '=', $eventId)
            ->where('visitorId', '=', $this->id)
            ->select('elementId')
            ->get();
        $structureManager = $this->getService('structureManager');
        foreach ($result as $row) {
            if (!array_key_exists($row['elementId'], $this->addedProducts)) {
                $product = $structureManager->getElementById($row['elementId']);
                if (!empty($product)) {
                    $this->addedProducts[$row['elementId']] = [
                        'title' => $product->getTitle(),
                        'url'   => $product->URL
                    ];
                }
            }
        }
    }

    public function getAddedProductsToShoppingBasket()
    {
        if (empty($this->addedProducts)) {
            $this->setAddedProductsToShoppingBasket();
        }
        return $this->addedProducts;
    }

    protected function setFeedbackAnswers()
    {
        $this->feedbacks = [];
        $eventLogger = $this->getService(EventsLog::class);
        $eventType = 'feedback';
        $db = $this->getService('db');
        $eventId = $eventLogger->getEventTypeId($eventType);
        $result = $db->table('event')
            ->where('typeId', '=', $eventId)
            ->where('visitorId', '=', $this->id)
            ->select('elementId')
            ->get();
        $structureManager = $this->getService('structureManager');
        foreach ($result as $row) {
            if (!array_key_exists($row['elementId'], $this->feedbacks)) {
                $feedback = $structureManager->getElementById($row['elementId']);
                $fields = $feedback->getGenericValues();
                foreach ($fields as $key => $field) {
                    $fieldElement = $structureManager->getElementById($key);
                    $this->feedbacks[$row['elementId']][$fieldElement->getTitle()] = $field;
                }
            }
        }
    }

    public function getFeedbacks()
    {
        if (empty($this->feedbacks)) {
            $this->setFeedbackAnswers();
        }
        return $this->feedbacks;
    }

    protected function setEmailsClickEvent()
    {
        $this->emailClicks = [];
        $eventLogger = $this->getService(EventsLog::class);
        $eventType = 'emailClick';
        $db = $this->getService('db');
        $eventId = $eventLogger->getEventTypeId($eventType);
        $result = $db->table('event')
            ->where('typeId', '=', $eventId)
            ->where('visitorId', '=', $this->id)
            ->select('id')
            ->get();
        foreach ($result as $row) {
            $link = $this->getLink($row['id']);
            $this->emailClicks[$link['uri']]++;
        }
    }

    public function getEmailClicks()
    {
        return $this->emailClicks;
    }

    protected function setSearchLog()
    {
        $db = $this->getService('db');
        $result = $result = $db->table('search_log')
            ->where('visitorId', '=', $this->id)
            ->select('phrase')
            ->get();
        foreach ($result as $row) {
            $this->searchQueries[$row['phrase']]++;
        }
    }

    public function getSearchLog()
    {
        return $this->searchQueries;
    }
}