<?php

use Illuminate\Database\Connection;

class VisitorsManager extends errorLogger
{
    const TABLE_VISIT = 'visit';
    const TABLE_VISITOR = 'visitor';
    const TABLE_ORDER = 'visitor_order';
    const TRACKING_CODE_COOKIE = 'tc';
    const VISIT_RECORDED_COOKIE = 'vr';
    protected $trackingCode = '';
    protected $currentVisitorLoaded = false;
    protected $currentVisitor;
    protected $visitationRecorded;

    public function __construct(
        protected Connection $statsDb,
    )
    {
        if (!empty($_COOKIE[self::TRACKING_CODE_COOKIE])) {
            $this->trackingCode = $_COOKIE[self::TRACKING_CODE_COOKIE];
        }
    }

    /**
     * @return null|Visitor
     */
    public function getCurrentVisitor()
    {
        if ($this->currentVisitorLoaded === false) {
            if ($this->trackingCode) {
                $this->currentVisitor = $this->findVisitor(function ($query) {
                    $query->where('trackingCode', $this->trackingCode);
                });
            }
        }
        return $this->currentVisitor;
    }

    public function recordVisit($referrer)
    {
        $visitor = null;
        if ($this->trackingCode) {
            $visitor = $this->findVisitor(function ($query) {
                $query->where('trackingCode', $this->trackingCode);
            });
        }
        if (!$visitor) {
            $this->setTrackingCode(uniqid());
            $visitor = new Visitor();
            $visitor->trackingCode = $this->trackingCode;
            $this->saveVisitor($visitor);
        }
        $this->currentVisitor = $visitor;
        $this->currentVisitorLoaded = true;
        $this->createVisitQuery()->insert([
            'time' => $_SERVER['REQUEST_TIME'],
            'visitorId' => $visitor->id,
            'referer' => $referrer,
        ]);
        $this->visitationRecorded = true;
        setcookie(self::VISIT_RECORDED_COOKIE, 1, 0, '/');
    }

    public function saveVisitor(Visitor $visitor)
    {
        $data = $visitor->getDataArray();
        if (!empty($data['user'])) {
            unset($data['user']);
        }
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }
        if (!empty($data['id']) && $data['id'] > 0) {
            $this->createVisitorQuery()
                ->where('id', '=', $data['id'])
                ->update($data);
        } else {
            $id = $this->createVisitorQuery()->insertGetId($data);
            $visitor->id = $id;
        }
    }

    protected function findVisitor(callable $conditions)
    {
        $query = $this->createVisitorQuery();
        if (!empty($this->currentVisitor)) {
            $query->select(array_keys($this->currentVisitor->getDataArray()));
        }
        $conditions($query);
        if ($data = $query->first()) {
            $visitor = new Visitor();
            $visitor->setData($data);
            return $visitor;
        }
        return null;
    }

    protected function findSimilarVisitors($data, $excludeId = false)
    {
        $result = [];
        $query = $this->createVisitorQuery();
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        $query->where(function ($query) use ($data) {
            foreach ($data as $key => $value) {
                $query->orWhere($key, 'like', $value);
            }
        }
        );

        if ($records = $query->get()) {
            foreach ($records as $record) {
                $visitor = new Visitor();
                $visitor->setData($record);
                $result[] = $visitor;
            }
        }
        return $result;
    }

    public function updateCurrentVisitorData($data)
    {
        if (!$data) {
            return;
        }
        if ($currentVisitor = $this->getCurrentVisitor()) {
            $similarSearchData = [];
            foreach ((array)$data as $key => $value) {
                if (in_array($key, ['email', 'userId'])) {
                    $similarSearchData[$key] = trim($value);
                }
                if (!$value) {
                    unset($data[$key]);
                }
                if ($currentVisitor->$key == $value) {
                    unset($data[$key]);
                } else {
                    $currentVisitor->$key = $value;
                }
            }
            $this->saveVisitor($currentVisitor);
            if ($similarSearchData && ($visitorsToMerge = $this->findSimilarVisitors($similarSearchData, $currentVisitor->id))) {
                $mainVisitor = array_shift($visitorsToMerge);
                $visitorsToMerge[] = $currentVisitor;
                foreach ($visitorsToMerge as $visitorToMerge) {
                    $this->mergeVisitors($mainVisitor, $visitorToMerge);
                }
                $this->setCurrentVisitor($mainVisitor);
            } else {
                $this->saveVisitor($currentVisitor);
            }
        } else {
            $this->recordVisitor($data);
        }
    }

    public function updateVisitor(Visitor $visitor)
    {
        if (!empty($visitor)) {
            $similarSearchData = [];
            $data = $visitor->getDataArray();
            foreach ((array)$data as $key => $value) {
                if (in_array($key, ['email', 'userId'])) {
                    if (!empty($value)) {
                        $similarSearchData[$key] = $value;
                    }
                }
                if (empty($data[$key])) {
                    unset($data[$key]);
                }
                if ($visitor->$key == $value) {
                    unset($data[$key]);
                } else {
                    $visitor->$key = $value;
                }
            }
            if ($visitor->userId != 0 || !empty($visitor->email)) {
                if (!empty($similarSearchData) && ($visitorsToMerge = $this->findSimilarVisitors($similarSearchData, $visitor->id))) {
                    $mainVisitor = array_shift($visitorsToMerge);
                    $visitorsToMerge[] = $visitor;
                    foreach ($visitorsToMerge as $visitorToMerge) {
                        $this->mergeVisitors($mainVisitor, $visitorToMerge);
                    }
                } else {
                    $this->saveVisitor($visitor);
                }
            }
        }
    }

    protected function recordVisitor($data)
    {
        if (!$data) {
            return;
        }
        if ($visitors = $this->findSimilarVisitors($data)) {
            $visitor = $visitors[0];
        } else {
            $visitor = new Visitor();
            $visitor->email = $data['email'];
        }
        if (empty($visitor->trackingCode)) {
            $data['trackingCode'] = uniqid();
        }
        $result = $this->getUserIdByEmail($visitor->email);
        if (!empty($result)) {
            $data['userId'] = $result['id'];
        }
        $this->setCurrentVisitor($visitor);
        $this->updateCurrentVisitorData($data);
    }

    public function isVisitationRecorded()
    {
        if ($this->visitationRecorded === null) {
            if (!empty($_COOKIE[self::VISIT_RECORDED_COOKIE])) {
                $this->visitationRecorded = $_COOKIE[self::VISIT_RECORDED_COOKIE] && $this->trackingCode;
            }
        }
        return $this->visitationRecorded;
    }

    public function saveCurrentVisitorOrder(orderElement $order)
    {
        if ($visitor = $this->getCurrentVisitor()) {
            $this->createOrderQuery()->insert([
                'visitorId' => $visitor->id,
                'orderId' => $order->id,
                'amount' => $order->getTotalPrice(),
            ]);
        }
    }

    protected function mergeVisitors(Visitor $from, Visitor $to)
    {
        $visitors = [$from, $to];
        $data = [];
        //merge data of two visitors into common array
        foreach ($visitors as $visitor) {
            foreach ($visitor as $key => $value) {
                if ($key != 'id') {
                    if (!empty($value) && empty($data[$key])) {
                        $data[$key] = $value;
                    }
                }
            }
        }
        //delete visitor data we merge info from
        $this->createVisitorQuery()
            ->where('id', $from->id)
            ->delete();
        //update tracking code in remaining visitor object
        $to->trackingCode = $from->trackingCode;

        //now save all data to remaining visitor object
        $this->createVisitorQuery()
            ->where('id', $to->id)
            ->update($data);

        //move all visits from older visitor to newer one
        $this->createVisitQuery()
            ->where('visitorId', $from->id)
            ->update(['visitorId' => $to->id]);

        //move all orders from older visitor to newer one
        $this->createOrderQuery()
            ->where('visitorId', $from->id)
            ->update(['visitorId' => $to->id]);
    }

    public function createVisitorQuery()
    {
        return $this->statsDb->table(self::TABLE_VISITOR);
    }

    public function createVisitQuery()
    {
        return $this->statsDb->table(self::TABLE_VISIT);
    }

    public function createOrderQuery()
    {
        return $this->statsDb->table(self::TABLE_ORDER);
    }

    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;
        setcookie(self::TRACKING_CODE_COOKIE, $this->trackingCode, time() + 365 * 24 * 60 * 60, '/');
    }

    public function setCurrentVisitor(Visitor $visitor)
    {
        $this->currentVisitor = $visitor;
        $this->setTrackingCode($visitor->trackingCode);
    }

    public function getVisitorIdFromEmail($email)
    {
        $data = [];
        $data['email'] = $email;

        $this->recordVisitor($data);

        $result = $this->createVisitorQuery()
            ->where('visitor.email',
                '=',
                $email)
            ->select('visitor.id')
            ->first();
        return $result['id'];
    }

    protected function getUserIdByEmail($userEmail = null)
    {
        if (!empty($userEmail)) {
            if ($result = $this->statsDb->table('module_user')->where('email', '=', $userEmail)->select('id')->limit(1)->get()) {
                return $result[0];
            }
        }
        return false;
    }
}