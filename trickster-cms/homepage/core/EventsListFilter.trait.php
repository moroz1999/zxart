<?php

trait EventsListFilterTrait
{
    protected $t_eventsIdList;
    protected $t_events;
    protected $t_groupedEvents;
    protected $t_filter;
    protected $eventsCalendar;
    protected $monthsInfo;
    protected $monthsInfoIndex;

    /**
     * returns the value of manually selected months filter dropdown in public
     * @return bool|int
     *
     * @deprecated
     */
    public function getFilter()
    {
        return $this->getSelectedMonthStamp();
    }

    /**
     * returns structured events (by list or by month)
     *
     * @param string $layout
     * @return array|null
     */
    public function getGroupedEvents($layout = 'month')
    {
        $method = 'regroupBy_' . $layout;
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return $this->regroupBy_month();
        }
    }

    /**
     * returns months information for months filter dropdown in public
     * @return array
     */
    public function getMonthsInfo()
    {
        $monthsInfo = [];
        $monthStamps = [];
        $db = $this->getService('db');

        $query = $db->table('module_event')
            ->selectRaw('min(startDate) as minStartDate, max(startDate) as maxStartDate, max(endDate) as maxEndDate')
            ->whereIn('id', $this->getCurrentEventsIdList());
        if ($record = $query->first()) {
            $dates = [];
            if ($record['minStartDate'] > 0) {
                $dates[] = $record['minStartDate'];
            }
            if ($record['maxStartDate'] > 0) {
                $dates[] = $record['maxStartDate'];
            }
            if ($record['maxEndDate'] > 0) {
                $dates[] = $record['maxEndDate'];
            }
            $minDate = min($dates);
            $maxDate = max($dates);

            $start = new DateTime();
            $start->setTimestamp($minDate);
            $start->modify('first day of this month');
            $end = new DateTime();
            $end->setTimestamp($maxDate);
            $end->modify('last day of this month');
            $interval = DateInterval::createFromDateString('1 month');
            $period = new DatePeriod($start, $interval, $end);

            foreach ($period as $dateTime) {
                $monthStamps[] = $dateTime->getTimestamp();
            }
        }

        foreach ($monthStamps as &$monthStamp) {
            $monthInfo = [];
            $monthInfo['stamp'] = $monthStamp;
            $monthInfo['month'] = date('n', $monthStamp);
            $monthInfo['year'] = date('Y', $monthStamp);
            $monthsInfo[$monthStamp] = $monthInfo;
        }

        return $monthsInfo;
    }

    protected function regroupBy_month()
    {
        if ($this->t_groupedEvents === null) {
            if ($events = $this->getEventsElements()) {
                $eventsGroupIndex = [];
                foreach ($events as $eventElement) {
                    $eventMonthStartStamp = strtotime('first day of this month ' . $eventElement->startDate);

                    if (!isset($eventsGroupIndex[$eventMonthStartStamp])) {
                        $eventsGroupIndex[$eventMonthStartStamp] = [];
                    }
                    $eventsGroupIndex[$eventMonthStartStamp][] = $eventElement;
                }
                $this->t_groupedEvents = $eventsGroupIndex;
            }
        }
        return $this->t_groupedEvents;
    }

    protected function regroupBy_list()
    {
        return $this->getEventsElements();
    }

    /**
     * Generates an associative array of months, each month is an array of weeks,
     * each week is an array of days, each day is an array containing the day number and its events
     */
    public function getEventsCalendar()
    {
        if (is_null($this->eventsCalendar)) {
            $this->eventsCalendar = [];
            $eventsMonthsIndex = $this->regroupBy_month();

            $todaysTimestamp = strtotime("00:00");

            // get months
            foreach ($eventsMonthsIndex as $monthStartStamp => &$monthEventsList) {
                $monthWeeksList = [];
                $weekNumber = 1;
                $weekMajorNumber = (int)date('W', $monthStartStamp); // what is the number of this week in year

                // loop all days of given month, group them into weeks, assign events to days
                $monthEndStamp = strtotime("last day of this month", $monthStartStamp);
                for ($dayTimestamp = $monthStartStamp; $dayTimestamp <= $monthEndStamp; $dayTimestamp = strtotime("+1 day", $dayTimestamp)) {
                    // check if we're dealing with a new week...
                    $iteratedWeekMajorNumber = (int)date('W', $dayTimestamp);
                    if ($iteratedWeekMajorNumber > $weekMajorNumber) {
                        $weekNumber++;
                    }
                    $dayItem = [];
                    $dayItem['currentDay'] = ($todaysTimestamp == $dayTimestamp);
                    $dayItem['pastDay'] = ($dayTimestamp < $todaysTimestamp);
                    $dayItem['dayNumber'] = date('j', $dayTimestamp); // day number (1-31)
                    $weekDayNumber = date('N', $dayTimestamp); // weekday number (1-7)
                    $dayItem['weekDayNumber'] = $weekDayNumber;
                    $dayItem['holiday'] = ($weekDayNumber == 7 || $weekDayNumber == 6);
                    $dayItem['events'] = [];
                    $currentDayNumber = date('j', $dayTimestamp);
                    foreach ($monthEventsList as &$eventElement) {
                        if ($eventElement->getStartDayNumber() == $currentDayNumber) {
                            $dayItem['events'][] = $eventElement;
                        }
                    }
                    $weekMajorNumber = $iteratedWeekMajorNumber;
                    $monthWeeksList[$weekNumber][$dayItem['weekDayNumber']] = $dayItem;
                }
                $this->eventsCalendar[$monthStartStamp] = $monthWeeksList;
            }
            ksort($this->eventsCalendar);
        }
        return $this->eventsCalendar;
    }

    public function getEventsMonthsInfo()
    {
        if ($this->monthsInfo === null) {
            $this->monthsInfo = [];
            $monthStamps = [];

            if ($idList = $this->getBaseEventsIdList()) {
                $db = $this->getService('db');

                $query = $db->table('module_event')
                    ->selectRaw('min(startDate) as minStartDate, max(startDate) as maxStartDate, max(endDate) as maxEndDate')
                    ->whereIn('id', $idList);
                if ($record = $query->first()) {
                    $dates = [];
                    if ($record['minStartDate'] > 0) {
                        $dates[] = $record['minStartDate'];
                    }
                    if ($record['maxStartDate'] > 0) {
                        $dates[] = $record['maxStartDate'];
                    }
                    if ($record['maxEndDate'] > 0) {
                        $dates[] = $record['maxEndDate'];
                    }
                    $minDate = min($dates);
                    $maxDate = max($dates);

                    $start = new DateTime();
                    $start->setTimestamp($minDate);
                    $start->modify('first day of this month');
                    $end = new DateTime();
                    $end->setTimestamp($maxDate);
                    $end->modify('last day of this month');
                    $interval = DateInterval::createFromDateString('1 month');
                    $period = new DatePeriod($start, $interval, $end);

                    foreach ($period as $dateTime) {
                        $monthStamps[] = $dateTime->getTimestamp();
                    }
                }
            }
            foreach ($monthStamps as &$monthStamp) {
                $monthInfo = [];
                $monthInfo['stamp'] = $monthStamp;
                $monthInfo['month'] = date('n', $monthStamp);
                $monthInfo['year'] = date('Y', $monthStamp);
                $this->monthsInfo[] = $monthInfo;
                $this->monthsInfoIndex[$monthStamp] = $monthInfo;
            }
        }
        return $this->monthsInfo;
    }

    /**
     * returns months information for calendar
     *
     * @return mixed
     */
    public function getEventsMonthsInfoIndex()
    {
        if (is_null($this->monthsInfoIndex)) {
            $this->getEventsMonthsInfo();
        }
        return $this->monthsInfoIndex;
    }

    public function getSelectedMonthStamp()
    {
        $controller = controller::getInstance();
        if ($monthFilter = $controller->getParameter("period")) {
            return (int)$monthFilter;
        }
        return false;
    }

    public function getSelectedEventsPreset()
    {
        if ($preset = controller::getInstance()->getParameter("preset")) {
            if (in_array($preset, $this->getAllowedPresets())) {
                return $preset;
            }
        }
        return false;
    }

    public function getAllowedPresets()
    {
        return ['thisweek', 'thismonth', 'thisyear', 'past'];

    }

    public function getSelectedPresetStamps()
    {
        switch ($this->getSelectedEventsPreset()) {
            case "thisweek":
                $result = [
                    strtotime('today'),
                    strtotime('Monday next week'),
                ];
                break;
            case "thismonth":
                $result = [
                    strtotime('today'),
                    strtotime('last day of this month'),
                ];
                break;
            case "thisyear":
                $result = [
                    strtotime('today'),
                    strtotime('last day of this year'),
                ];
                break;
            case "past":
                $result = [
                    0,
                    strtotime('today'),
                ];
                break;
            default:
                $result = false;
                break;
        }
        return $result;
    }

    public function getCurrentEventsIdList()
    {
        if ($this->t_eventsIdList === null) {
            $this->t_eventsIdList = [];

            $db = $this->getService('db');

            $query = $db->table('module_event')->select('id')->distinct();
            $query->whereIn('id', $this->getBaseEventsIdList());

            if ($filter = $this->getSelectedMonthStamp()) {
                $filterMonth = date('m', $filter);
                $filterYear = date('Y', $filter);
                $filterMonthEndStamp = strtotime('last day of this month ' . "01." . $filterMonth . "." . $filterYear);

                $query->where(function ($query) use ($filter, $filterMonthEndStamp) {
                    $query->where('startDate', '<=', $filterMonthEndStamp)
                        ->where('endDate', '>=', $filter)
                        ->orWhereBetween('startDate', [$filter, $filterMonthEndStamp]);
                });
            } elseif ($stamps = $this->getSelectedPresetStamps()) {
                if ($this->getSelectedEventsPreset() === 'past') {
                    $query->where('endDate', '<=', $stamps[1]);
                    $query->where('endDate', '!=', 0);
                } else {
                    $query->where(function ($query) use ($stamps) {
                        $query->where('startDate', '<=', $stamps[1])
                            ->where('endDate', '>=', $stamps[0])
                            ->orWhereBetween('startDate', [$stamps[0], $stamps[1]]);
                    });
                }
            }

            if ($this->sort == 'asc') {
                $query->orderBy('startDate', 'asc');
            } else {
                $query->orderBy('startDate', 'desc');
            }

            if ($this->displayLimit != 0) {
                $query->limit((int)$this->displayLimit);
            }

            if ($result = $query->get()) {
                $this->t_eventsIdList = array_column($result, 'id');
            }
        }
        return $this->t_eventsIdList;
    }

    abstract protected function getBaseEventsIdList();

    abstract public function getConnectedEventsLists();

    abstract public function getEventsElements();

}